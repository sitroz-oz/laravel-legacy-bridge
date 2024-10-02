# laravel-legacy-bridge ( LaraBridge )
This package allows you to use all the familiar and powerful functionality of laravel as a library for an existing project.

## Возможности

- **Интеграция Laravel**: Использование мощных возможностей Laravel в любом проекте на PHP.
- **Обработка HTTP-запросов**: Посредством `LaraBridge` можно обрабатывать HTTP-запросы, так как это делает Laravel, достаточно включить файл инициализации.
- **Включение ваших файлов зависимостей**: Пакет в первую очередь направлен на то, чтобы файлы с вашими функциями были всегда загружены и доступны из любой части: в любом месте Laravel и, конечно, в коде вашего проекта.


- **Автоматическая установка**: В пакет включены скрипты для автоматической регистрации 
`LaraBridgeServiceProvider` и модификации загрузки Laravel.
- **Автоматическое удаление**: Вы можете запустить скрипт, который полностью удалит пакет 
и вернет все модифицированные файлы в исходное состояние.
- **Проверка и откат изменений**: Скрипты установки и удаления проверяют, успешно ли 
проходит каждый этап установки и выполняет откат изменений чтобы ваш проект не переставал работать.

## Requirements

- **Laravel**: 5.4
- **PHP**: 5.6

## Installation

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


## Использование
Например ваш проект лежит в папке
`/path1/path2/my-project`, тогда 
- установите laravel рядом, в `/path1/path2/laravel` не важно как будет назваться папка с проектом.

- Установите и настройте пакет laravel-legacy-bridge используя composer в `/path1/path2/laravel`
- Создайте файл `/path1/path2/my-project/bridgeExample.php`
    ```PHP
    <?php
    
    include_once __DIR__.'../laravel/bootstrap/init.php';
    
    dump('Hello world from Laravel!');
    ```
- Вы можете подключать bootstrap/init.php в любом файле, но чтобы сделать это более красиво и ремонтопригодно
рекомендую определить константу LARAVEL_INIT, которая укажет на этот файл и добавить её в глобальные константы PHP.


## Вклад в проект

Ваш вклад приветствуется! Пожалуйста, отправляйте pull-реквесты и создавайте issues для улучшения этого пакета.

## Лицензия

Этот проект лицензирован на условиях MIT. Подробности можно найти в файле LICENSE.