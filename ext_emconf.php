<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'CSV Labels',
    'description' => 'Use CSV files to provide translation labels to TYPO3',
    'category' => 'misc',
    'author' => 'Simon Praetorius',
    'author_email' => 'praetorius@sitegeist.de',
    'author_company' => 'sitegeist media solutions GmbH',
    'state' => 'stable',
    'uploadfolder' => false,
    'clearCacheOnLoad' => true,
    'version' => '1.0.2',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-11.9.99',
            'php' => '7.2.0-7.9.99'
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Sitegeist\\CsvLabels\\' => 'Classes'
        ]
    ],
];
