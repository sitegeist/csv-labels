<?php

call_user_func(function () {
    // Add csv files to language format priorities
    $languageFilePriority = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(
        ',',
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['lang']['format']['priority']
    );
    $languageFilePriority[] = 'csv';
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['lang']['format']['priority'] = implode(',', $languageFilePriority);

    // Register CSV parser for language files
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['lang']['parser']['csv'] = \Sitegeist\CsvLabels\Localization\CsvLocalizationParser::class;
});
