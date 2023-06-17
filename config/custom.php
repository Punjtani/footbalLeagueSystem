<?php

return [
    'custom' => [
        'theme' => 'light',                     // options[String]: 'light'(default), 'dark', 'semi-dark'
        'sidebarCollapsed' => false,            // options[Boolean]: true, false(default)
        'navbarColor' => '',                    // options[String]: bg-primary, bg-info, bg-warning, bg-success, bg-danger, bg-dark (default: '' for #fff)
        'navbarType' => 'floating',             // options[String]: floating(default) / static / sticky / hidden
        'footerType' => 'static',               // options[String]: static(default) / sticky / hidden
        'bodyClass' => '',                       // add custom class
    ],

    'status' => [
        '' => '',
        0 => 'Draft',
        1 => 'Published',
        2 => 'Inactive',
    ],
    
    'subscription_status' => [
        '' => '',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'pending' => 'Pending Payment',
    ],

    'template_types' => [
        0 => 'Knockout (Single Elimination)',
//        1 => 'Knockout (Double Elimination)',
        2 => 'League Format',
        3 => 'Round Robin with Knockout',
    ],

    'sport_types' => [
        0 => 'Football',
        1 => 'Rugby',
        2 => 'Cricket',
    ],

    'itemPerPage' => 10,

    'textAreaCharacterLimit' => 350,
];
