<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Certificate Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for certificate generation and management.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Logo Path
    |--------------------------------------------------------------------------
    |
    | Default logo path if no logo is uploaded for a certificate.
    |
    */
    'default_logo_path' => null,

    /*
    |--------------------------------------------------------------------------
    | Logo Storage Disk
    |--------------------------------------------------------------------------
    |
    | The disk where certificate logos are stored.
    |
    */
    'logo_disk' => 'public',

    /*
    |--------------------------------------------------------------------------
    | Logo Storage Path
    |--------------------------------------------------------------------------
    |
    | The path within the storage disk where logos are stored.
    |
    */
    'logo_path' => 'certificates/logos',

    /*
    |--------------------------------------------------------------------------
    | PDF Format
    |--------------------------------------------------------------------------
    |
    | Default PDF format for certificate generation.
    |
    */
    'pdf_format' => 'a4',

    /*
    |--------------------------------------------------------------------------
    | Certificate Number Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix for auto-generated certificate numbers.
    |
    */
    'certificate_number_prefix' => 'CERT-',

    /*
    |--------------------------------------------------------------------------
    | Certificate Number Length
    |--------------------------------------------------------------------------
    |
    | Length of the random part of certificate numbers.
    |
    */
    'certificate_number_length' => 8,
];

