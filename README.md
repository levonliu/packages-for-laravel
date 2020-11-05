## Packages-For-Laravel

[![license](https://img.shields.io/badge/license-WTFPL%20--%20Do%20What%20the%20Fuck%20You%20Want%20to%20Public%20License-green.svg)](https://raw.githubusercontent.com/ALawating-Rex/packagetest-for-laravel/master/LICENSE)


## Installation and Configuration

Install the current version of the `levonliu/packages-for-laravel` package via composer:
```shell
$ composer require "levonliu/packages-for-laravel:dev-master" -vvv
```
### Laravel
The package's service provider will automatically register its service provider.

Publish the configuration file:
```sh
php artisan vendor:publish --provider="Levonliu\Packages\Service\ServiceProvider" 
```

##### Alternative configuration method via .env file

After you publish the configuration file as suggested above, you may configure service packages
by adding the following to your application's `.env` file (with appropriate values):
  
```ini
REQUEST_BASE_URI=
REQUEST_CURL_TIMEOUT=240
REQUEST_VERIFY=false
```