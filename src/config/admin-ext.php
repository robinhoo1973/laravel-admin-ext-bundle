<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel-Admin-Extension-Bundle Language Selector Settings
    |--------------------------------------------------------------------------
    |
    | Here are language selector settings for Laravel-Admin-Extension-Bundle.
    |
    */

    'lang-selector' => [
        'form' => [
            // Language Selector Form Settings.

            'id' => 'lang-selector',
            // URl should set without Laravel Admin Prefix 
            'url' => '/api/_lang_switcher',
            // You may also add the laravel admin route file about this record with corresponding method
            'method' => 'post',
        ],
        'data' => [
            'class' => App\Model\Locale::class,
            'method' => 'getLocalesWithFlag',
            'fields' => [
                'flag' => 'flag',        //national flag field
                'locale' => 'id',    //locale code field
                'native' => 'native',   //native language name field which value would be used for display 
            ],
            'field' => 'locale',    //form submit field
        ],
        'enable' => true,

    ],

    //the field name of the storage in  cookie
    'field' => 'locale',
];
