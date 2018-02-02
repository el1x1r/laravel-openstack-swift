# Laravel OpenStack Swift

OpenStack Swift storage driver for Laravel 5.

## Installation

Require the package with Composer:

```
composer require mzur/laravel-openstack-swift
```

For Laravel 5.4 and lower, add the service provider to `config/app.php`:

```php
Mzur\Filesystem\SwiftServiceProvider::class,
```

## Configuration

Add a new storage disk to `config/filesystems.php` (using v3 of the identity API):

```
'disks' => [
   'openstack' => [
      'driver'    => 'swift',
      'authUrl'   => env('OS_AUTH_URL', ''),
      'region'    => env('OS_REGION_NAME', ''),
      'user'      => env('OS_USERNAME', ''),
      'password'  => env('OS_PASSWORD', ''),
      'projectId' => env('OS_PROJECT_ID', ''),
      'container' => env('OS_CONTAINER_NAME', ''),
   ],
]
```
