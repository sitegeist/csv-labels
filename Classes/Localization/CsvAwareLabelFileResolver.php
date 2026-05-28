<?php
declare(strict_types=1);
namespace Sitegeist\CsvLabels\Localization;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TYPO3\CMS\Core\Localization\Exception\FileNotFoundException;
use TYPO3\CMS\Core\Localization\LabelFileResolver;

#[AsDecorator(
    decorates: LabelFileResolver::class,
    onInvalid: ContainerInterface::IGNORE_ON_INVALID_REFERENCE,
)]
#[Autoconfigure(public: true)]
final readonly class CsvAwareLabelFileResolver extends LabelFileResolver
{
    public function resolveFileReference(string $fileReference, string $locale): ?string
    {
        $resolvedFileReference = parent::resolveFileReference($fileReference, $locale);
        if ($resolvedFileReference !== null || $locale === 'default') {
            return $resolvedFileReference;
        }

        try {
            $baseFileReference = $this->getAbsoluteFileReference($fileReference);
        } catch (FileNotFoundException) {
            return null;
        }

        if (strtolower((string)pathinfo($baseFileReference, PATHINFO_EXTENSION)) !== 'csv') {
            return null;
        }

        return $baseFileReference;
    }
}
