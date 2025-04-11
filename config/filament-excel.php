<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Disk Settings
    |--------------------------------------------------------------------------
    |
    | Configure the disk to use for storing Excel exports. You can use any
    | disk configured in your filesystems.php config file.
    |
    | Supported: "local", "s3", "sftp", etc.
    |
    */
    'disk' => env('FILAMENT_EXCEL_DISK', 'filament-excel'),
    
    /*
    |--------------------------------------------------------------------------
    | Disk Driver
    |--------------------------------------------------------------------------
    |
    | Specify the driver of the disk. If using S3, the package will inherit
    | S3 configuration from your filesystems.php s3 disk configuration.
    |
    | Supported: "local", "s3"
    |
    */
    'disk_driver' => env('FILAMENT_EXCEL_DISK_DRIVER', 'local'),
    
    /*
    |--------------------------------------------------------------------------
    | S3 Path Prefix
    |--------------------------------------------------------------------------
    |
    | When using the S3 driver, this is the path prefix within your S3 bucket
    | where files will be stored. This replaces the 'root' setting used for
    | local disks.
    |
    */
    's3_path' => env('FILAMENT_EXCEL_S3_PATH', 'filament-excel'),
    
    /*
    |--------------------------------------------------------------------------
    | Auto-Delete Files
    |--------------------------------------------------------------------------
    |
    | Automatically delete files after they have been downloaded.
    |
    */
    'auto_delete_after_download' => env('FILAMENT_EXCEL_AUTO_DELETE', true),
    
    /*
    |--------------------------------------------------------------------------
    | Temporary URL Expiration
    |--------------------------------------------------------------------------
    |
    | When using S3, this setting controls how long URLs are valid for.
    | This is only used when generating S3 temporary URLs.
    | Value is in minutes.
    |
    */
    'temporary_url_expiration' => (int)env('FILAMENT_EXCEL_URL_EXPIRATION', 24 * 60), // in minutes (24 hours default)
];