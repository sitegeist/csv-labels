<?php
declare(strict_types=1);
namespace Sitegeist\CsvLabels\Translation\Loader;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[Autoconfigure(tags: [['name' => 'translation.loader', 'format' => 'csv']])]
class CsvLoader implements LoaderInterface
{
    private ?Locales $locales = null;

    public function load(mixed $resource, string $locale, string $domain = 'messages'): MessageCatalogue
    {
        if (!file_exists($resource)) {
            throw new NotFoundResourceException(\sprintf('File "%s" not found.', $resource), 1612282091);
        }

        try {
            $file = new \SplFileObject($resource, 'r');
            $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);
        } catch (\RuntimeException $e) {
            throw new InvalidResourceException(\sprintf('File "%s" could not be opened.', $resource), 1767019766, $e);
        }

        if (!$file->valid()) {
            throw new InvalidResourceException(\sprintf('File "%s" has invalid CSV content.', $resource), 1767623886);
        }

        $csvHeader = $file->current();
        if ($csvHeader === false) {
            return new MessageCatalogue($locale);
        }

        $this->locales = GeneralUtility::makeInstance(Locales::class);
        $localeColumnIndex = $this->searchLocaleColumnIndex($csvHeader, $locale);
        if ($localeColumnIndex === false) {
            return new MessageCatalogue($locale);
        }

        foreach ($file as $lineNumber => $translation) {
            if ($lineNumber === 0 || empty($translation[0])) {
                continue;
            }
            $identifier = $translation[0];
            if (isset($translation[$localeColumnIndex]) && $translation[$localeColumnIndex] !== '') {
                $messages[$identifier] =  $translation[$localeColumnIndex];
            }
        }

        return new MessageCatalogue($locale, [$domain => $messages]);
    }

    private function searchLocaleColumnIndex(array $csvHeader, string $locale): int|false
    {
        $localeChain = $this->buildLocaleFallbackChain($locale, $this->locales);

        foreach ($localeChain as $currentLocale) {
            $index = array_search($currentLocale, $csvHeader, true);
            if ($index !== false) {
                return $index;
            }
        }

        return false;
    }

    private function buildLocaleFallbackChain(string $locale, Locales $locales): array
    {
        $chain = [$locale];

        // e.g. de-DE -> de
        if (str_contains($locale, '-')) {
            [$baseLanguage] = explode('-', $locale, 2);
            $chain[] = $baseLanguage;
        } elseif (str_contains($locale, '_')) {
            [$baseLanguage] = explode('_', $locale, 2);
            $chain[] = $baseLanguage;
        }

        $chain = array_merge($chain, $this->locales->getLocaleDependencies($locale));

        $chain[] = 'default';

        return array_unique($chain);
    }
}
