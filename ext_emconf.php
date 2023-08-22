<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Pagedoctor',
    'description' => 'Rapidly build and deploy content elements for TYPO3 CMS without one line of code.',
    'category' => 'be',
    'state' => 'beta',
    'author' => 'Colin Atkins',
    'author_email' => 'atkins@hey.com',
    'uploadfolder' => '0',
    'version' => '0.5.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0-12.1.999',
        ],
        'conflicts' => [],
        'suggests' => [
            'setup' => '12.0.0-12.1.999',
        ],
    ],
];