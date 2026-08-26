<?php

declare(strict_types=1);

namespace Sitegeist\CsvLabels\Command;

use TYPO3\CMS\Core\Localization\TranslationDomainResolver;
use TYPO3\CMS\Core\Localization\Loader\XliffLoader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'csv-labels:convert',
    description: 'Convert XLIFF label files to CSV label files.',
)]
final class ConvertXliffCommand extends Command
{
    public function __construct(
        private readonly TranslationDomainResolver $translationDomainResolver,
        private readonly XliffLoader $xliffLoader,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'xlf',
            InputArgument::REQUIRED,
            'Explicit XLIFF file to convert.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $labelsFilePath = (string) $input->getArgument('xlf');

        if (!$this->isConvertibleXliffFile($labelsFilePath)) {
            $io->error(sprintf('XLIFF file "%s" does not exist or is not an XLIFF file.', $labelsFilePath));
            return Command::INVALID;
        }

        $directory = dirname($labelsFilePath);
        $labelsFilename = basename($labelsFilePath);
        $translationFiles = $this->findTranslationXliffFiles($directory, $labelsFilename);

        if ($translationFiles === []) {
            $io->warning(sprintf('No matching translation files found for "%s".', $labelsFilePath));
            return Command::SUCCESS;
        }

        $files = [$labelsFilePath, ...$translationFiles];
        $outputFile = $this->buildOutputFilePath($labelsFilePath);
        $result = $this->convertGroup($files, $outputFile);
        if ($result === null) {
            $io->warning(sprintf('No labels could be converted for "%s".', $labelsFilePath));
            return Command::SUCCESS;
        }

        [$locales, $filesToRemove] = $result;
        $io->title('XLIFF labels converted');
        $io->text(sprintf('CSV file: %s', $outputFile));
        $io->section('Locales');
        $io->listing(array_merge(['default'], $locales));
        $io->section('XLIFF files to remove');
        $io->listing($filesToRemove);
        $io->success('Done.');

        return Command::SUCCESS;
    }

    /**
     * @param list<string> $files
     *
     * @return array{0:list<string>,1:list<string>}|null
     */
    private function convertGroup(array $files, string $outputFile): ?array
    {
        $entries = [];
        $locales = [];
        $defaultFile = null;

        foreach ($files as $file) {
            $parsed = $this->parseFile($file);
            if ($parsed === null) {
                continue;
            }

            if ($parsed['locale'] === 'default') {
                $defaultFile = $parsed;
            } else {
                $locales[] = $parsed['locale'];
            }

            foreach ($parsed['entries'] as $id => $value) {
                $entries[$id] ??= [];
                $entries[$id][$parsed['locale']] = $value;
            }
        }

        if ($entries === [] || $defaultFile === null) {
            return null;
        }

        $orderedIds = array_keys($defaultFile['entries']);
        foreach (array_keys($entries) as $id) {
            if (!in_array($id, $orderedIds, true)) {
                $orderedIds[] = $id;
            }
        }

        $locales = array_values(array_unique(array_filter($locales)));
        sort($locales, SORT_NATURAL | SORT_FLAG_CASE);

        $handle = fopen($outputFile, 'wb');
        if ($handle === false) {
            throw new \RuntimeException(sprintf('Could not open "%s" for writing.', $outputFile));
        }

        $header = ['identifier', 'default', ...$locales];
        fputcsv($handle, $header, ',', '"', '');

        foreach ($orderedIds as $id) {
            $row = [$id, $defaultFile['entries'][$id] ?? ''];

            foreach ($locales as $locale) {
                $row[] = $entries[$id][$locale] ?? '';
            }

            fputcsv($handle, $row, ',', '"', '');
        }

        fclose($handle);

        return [
            $locales,
            array_map(static fn (string $file): string => $file, $files),
        ];
    }

    /**
     * @return list<string>
     */
    private function findTranslationXliffFiles(string $directory, string $labelsFilename): array
    {
        $finder = Finder::create()
            ->files()
            ->in($directory)
            ->depth('== 0')
            ->name('*.' . $labelsFilename)
            ->sortByName();

        $files = [];
        foreach ($finder as $file) {
            $files[] = $file->getPathname();
        }

        return $files;
    }

    /**
     * @return array{locale:string, entries: array<string, string>}|null
     */
    private function parseFile(string $file): ?array
    {
        $locale = $this->resolveLocale($file);

        try {
            $catalogue = $this->xliffLoader->load($file, $locale);
        } catch (\Throwable) {
            return null;
        }

        $entries = [];
        foreach ($catalogue->all('messages') as $id => $value) {
            if (!is_string($value)) {
                continue;
            }

            $entries[$id] = $value;
        }

        return [
            'locale' => $locale,
            'entries' => $entries,
        ];
    }

    private function buildOutputFilePath(string $labelsFilePath): string
    {
        return dirname($labelsFilePath) . DIRECTORY_SEPARATOR . pathinfo($labelsFilePath, PATHINFO_FILENAME) . '.csv';
    }

    private function resolveLocale(string $file): string
    {
        return $this->translationDomainResolver->getLocaleFromLanguageFile(basename($file)) ?? 'default';
    }

    private function isConvertibleXliffFile(string $path): bool
    {
        if (!is_file($path)) {
            return false;
        }

        return strtolower((string) pathinfo($path, PATHINFO_EXTENSION)) === 'xlf';
    }
}
