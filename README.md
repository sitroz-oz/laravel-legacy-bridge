# laravel-legacy-bridge ( LaraBridge )
This package allows you to use all the familiar and powerful functionality of laravel as a library for an existing project.

## Possibilities

- **Laravel Integration**: Use the power of Laravel in any PHP project.
- **HTTP Request handling**: With `LaraBridge` you can handle HTTP requests the way Laravel does it, just include the initialization file.
- **Including your dependency files**: The package primarily aims to ensure that your function files are always loaded and accessible from anywhere: anywhere in Laravel and, of course, in your project code.
- **Does not interfere with standard Laravel behavior**: use `public/index.php` and `artisan` as usual
- **Simple disable error handling**: Laravel error handler is very strong. If you are not sure that your project does not contain any warnings or errors, you can manage it.


- **Automatic installation**: The package includes scripts for automatic registration 
`LaraBridgeServiceProvider` and Laravel loading modifications.
- **Automatic removal**: You can run a script that will completely remove the package 
and will return all modified files to their original state.
- **Verifying and Rolling Back Changes**: Installation and uninstallation scripts check whether the 
goes through each stage of installation and rolls back changes so that your project does not stop working.

## Requirements

- **Laravel**: 5.4
- **PHP**: 5.6

## Usage

Suppose your project is located in the `/path1/path2/my-project` directory. Follow these steps:

1. [Install Laravel](https://laravel.com/docs/) next to your project, for example in the `/path1/path2/laravel` directory. The name of the folder containing Laravel does not matter.

2. Install and configure the `laravel-legacy-bridge` package using Composer in `/path1/path2/laravel`.

3. Create a file `/path1/path2/my-project/bridgeExample.php` with the following content:
    ```php
    <?php
    
    include_once __DIR__ . '/../laravel/bootstrap/init.php';
    
    dump('Hello world from Laravel!');
    ```

4. You can include `bootstrap/init.php` in any file.

To make this more elegant and maintainable, we recommend to define a `LARAVEL_INIT` constant equal to `path/to/bootstrap/init.php` and add it to the global PHP constants.
Then your `bridgeExample.php` will be like:
```php
<?php

include_once LARAVEL_INIT;

dump('Hello world from Laravel!');
 ```

## Installation

Here we suppose you have a laravel installation. If laravel is not installed, start from the topic before.

1. Install the package via composer
   ```CLI 
   composer require sitroz/laravel-legacy-bridge
   ```

2. Then run the register-script.php to register the LaraBridgeServiceProvider in the config app.php
   ```CLI
   php ./vendor/sitroz/laravel-legacy-bridge/register-script.php
   ```
   or place service provider to your `app.providers` config
   ```PHP
   Sitroz\LaraBridge\LaraBridgeServiceProvider::class,
   ```

3. Run the artisan command to embed the code into the standard files and push other files
   ```CLI
   php artisan laraBridge:install
   ```

4. Open `config/laraBridge.php` file and set your old boot files or other parameters as you wish

5. Include `.../laravel/bootstrap/init.php` file in any php file outside the Laravel folder
   You can also include your boot file in the LaraBridge configuration and include `init.php` 
   to your boot file, protection against recursive includes is configured here.

## UNINSTALL

1. Run artisan remove command and remove package in auto mode or follow the instructions if there is any issues.
    ```CLI
    php artisan laraBridge:remove
    ```
2. Remove `LaraBridgeServiceProvider::class` from `app.providers` config
3. Remove package via composer
    ```CLI
    composer remove sitroz/laravel-legacy-bridge
    ```

## Error Handling

By default, laravel handles exceptions and render an exception-page.
But if you are not sure that your application is 100% stable
and work without errors/warnings/etc. , then it is recommended
to disable this behavior so that the application
does not break by using laravel.

Important: you can disable error handling only for HTTP requests, CLI-scripts behaviour won't be changed.

For example:
```PHP
include_once __DIR__ . '/laravel/bootstrap/init.php';

$a[0] = 'Hello';
$a[2] = 'World';

echo $a[0].$a[1].$a[2]; // By default, here your script will be stopped by laravel

function smthImportant(){ 
    echo 'doing our important stuff';
}

smthImportant();
```

So the `smthImportant` won't be done.

### Disabling

To solve it you can disable that by setting config parameter `laraBridge.handling_exceptions.disabled` to `TRUE`.
In this case you will see a warning and `smthImportant` be done.

### Enable Parameter 
However, if you want to use an ErrorHandler, you can choose the `name` and the `secret` in 
`laraBridge.handling_exceptions.enable_param` config. Then you can enable handler for a single request you do.

Param usage: `http://example.com/index.php?name=secret`

To protect it from unauthorized use, you can control the configuration value from the ServiceProviders.
 

## TODO

- Create some functions in standalone php file
- Include it to config
- Test how it works
- Try to break while testing
- Try to write PHPUnit test with full workflow including installation, tests and removing the package.


- Test with next laravel versions


## Contribution to the project

Your contribution is welcome! Please send pull requests and create issues to improve this package.

## License

This project is licensed under MIT terms. Details can be found in the LICENSE file.