<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Connection Name
    |--------------------------------------------------------------------------
    */

    'default' => 'main',

    /*
    |--------------------------------------------------------------------------
    | Hashids Connections
    |--------------------------------------------------------------------------
    */

    'connections' => [

        'main' => [
            // Menggunakan tahun 2026 sebagai bagian dari salt rahasia
            'salt' => 'SIPINTAR_SPELAND_CORE_SECRET_2026_PRO', 
            
            // Link akan memiliki panjang minimal 8 karakter
            'length' => 8, 
            
            // Karakter yang diizinkan dalam URL
            'alphabet' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
        ],

        'alternative' => [
            'salt' => 'ALT_SECRET_2026',
            'length' => 12,
            'alphabet' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
        ],

    ],

];