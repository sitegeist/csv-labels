<?php
declare(strict_types=1);

use Sitegeist\CsvLabels\Translation\Loader\CsvLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

// Add csv files to language format priorities
$languageFilePriority = GeneralUtility::trimExplode(
    ',',
    $GLOBALS['TYPO3_CONF_VARS']['LANG']['format']['priority']
);
$languageFilePriority[] = 'csv';
$GLOBALS['TYPO3_CONF_VARS']['LANG']['format']['priority'] = implode(',', $languageFilePriority);

// Register CSV loader for language files
$GLOBALS['TYPO3_CONF_VARS']['LANG']['loader']['csv'] = CsvLoader::class;
