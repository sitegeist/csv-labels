<?php

call_user_func(function () {
    $languageFilePriority = explode(',', $GLOBALS['TYPO3_CONF_VARS']['SYS']['lang']['format']['priority']);
    $languageFilePriority[] = 'csv';
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['lang']['format']['priority'] = implode(',', $languageFilePriority);

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['lang']['parser']['csv'] = \Sitegeist\CsvLabels\Localization\CsvLocalizationParser::class;
});
