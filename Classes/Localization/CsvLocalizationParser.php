<?php
declare(strict_types=1);
namespace Sitegeist\CsvLabels\Localization;

use TYPO3\CMS\Core\Localization\Exception\FileNotFoundException;
use TYPO3\CMS\Core\Localization\Parser\LocalizationParserInterface;

class CsvLocalizationParser implements LocalizationParserInterface
{
    /**
     * Returns parsed representation of CSV language labels file
     *
     * @param string $sourcePath Source file path
     * @param string $languageKey Language key
     * @return array
     * @throws \TYPO3\CMS\Core\Localization\Exception\FileNotFoundException
     */
    public function getParsedData($sourcePath, $languageKey): array
    {
        if (!@is_file($sourcePath)) {
            throw new FileNotFoundException('Localization csv file does not exist', 1612282091);
        }

        $handle = fopen($sourcePath, 'r');
        $header = fgetcsv($handle);

        $LOCAL_LANG = array_fill_keys($header, []);

        if (!in_array($languageKey, $header)) {
            throw new FileNotFoundException('Localization csv file does not contain requested language', 1718634465);
        }

        while (($line = fgetcsv($handle)) !== false) {
            $identifier = $line[0];

            for ($i = 2, $count = count($line); $i < $count; $i++) {
                if ($line[$i] === '') {
                    continue;
                }
                $LOCAL_LANG[$header[$i]][$identifier] = [
                    0 => [
                        'target' => $line[$i]
                    ]
                ];
                if (isset($LOCAL_LANG['default'][$identifier][0]['target'])) {
                    $LOCAL_LANG[$header[$i]][$identifier][0]['source']
                        = $LOCAL_LANG['default'][$identifier][0]['target'];
                }
            }
        }

        fclose($handle);
        unset($LOCAL_LANG['identifier']);
        unset($LOCAL_LANG['description']);
        return $LOCAL_LANG;
    }
}
