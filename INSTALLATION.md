## INSTALLATION

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
  
3. Run the artisan command to embed the code into the standard files
   ```CLI
   php artisan laraBridge:install
   ```
   
4. Open `config/laraBridge.php` file and set your old boot files or other parameters as you wish
  
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