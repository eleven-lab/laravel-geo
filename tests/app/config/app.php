<?php

return [
    'name' => 'Laravel Geo',
    'env' => 'testing',
    'debug' => true,
    'url' => 'http://localhost',
    'asset_url' => null,
    'timezone' => 'Asia/Jakarta',
    'locale' => 'id',
    'fallback_locale' => 'en',
    'faker_locale' => 'id_ID',
    'key' => 'base64:0eTa63CqxDfkDH9nM9ZcWaZK6CxOEAwZemko9LoHdag=',
    'cipher' => 'AES-256-CBC',
    'providers' => [
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Karomap\GeoLaravel\DatabaseServiceProvider::class,
    ],
    'aliases' => [],

];
