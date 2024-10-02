<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Your requirements, what need include before booting laravel application
    |--------------------------------------------------------------------------
    |   Provide a list of files that will be included before booting the Laravel application.
    |   The requirements files are connected along a relative path from root_path, like
    |       require_once %root_path% / %path/to/requirement1.php%
    |
    |   By default, root_path points to the folder above the laravel installation,
    |   but if you have a different structure, change this.
    |
    */
    'root_path' => base_path('..'),
    'requirements' => [
        // path/from/root_path
    ],

    /*
    |--------------------------------------------------------------------------
    | Using laravel router to handle your requests.
    |--------------------------------------------------------------------------
    |   If use_router is false Laravel router won't try handle request
    |   by including file .../bootstrap/init.php
    |
    |   else mismatch with registered routes not cause any errors/exceptions,
    |   but you can use routing as example table below.
    |   If laravel handles request by router, 
    |   it will send response just after "include .../init.php" and die
    |
    |   URL to file with included init.php          | Default Router Path | Router path with 'rewrite_path_info' option
    |  ---------------------------------------------|---------------------|--------------------------------------------
    |   domain.com/folder1/folder2/index.php        | /                   | /folder1/folder2
    |   domain.com/folder1/folder2/index.php/p1/p2  | /p1/p1              | /folder1/folder2/p1/p2
    |   domain.com/folder1/folder2/page1.php        | /                   | /folder1/folder2/page1
    |   domain.com/folder1/folder2/page1.php/p1/p2  | /p1/p1              | /folder1/folder2/page1/p1/p2
    |
    */
    
    'use_router' => FALSE,
    'rewrite_path_info' => FALSE,

    /*
    |--------------------------------------------------------------------------
    | Handling exceptions
    |--------------------------------------------------------------------------
    |   By default laravel handles exceptions and render an exception-page.
    |   But if you are not sure that your application is 100% stable 
    |   and work without errors/warnings/etc. , then it is recommended 
    |   to disable this behavior so that the application 
    |   does not break by using laravel
    |   
    |   Default for laralib is: 'handling_exceptions.disabled' => True
    |
    |   In addition you have a request param, which can enable exceptions handler
    |   for example: 
    |       'name' => 'withErrors', 
    |       'value' => 'mySecret'
    |   Using:
    |       GET https://example.com/path/to/file.php?withErrors=mySecret
    |   Set 'name' to NULL to disable this behaviour
    |
    */
    
    'handling_exceptions' => [
        'disabled' => TRUE,
        
        'enable_param' => [
            'name' => NULL,
            'value' => NULL,
        ]
    ]
];
