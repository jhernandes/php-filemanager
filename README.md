# PHP FILEMANAGER

## Receive image files and pdf from POST and save on your server

## How to install

> composer require "jhernandes/php-filemanager"

## How to use

```php
<?php

require_once 'vendor/autoload.php';

$filemanager = new FileManager();

try {
    $filemanager->save('file_post_param', './uploads/images', 'new_file_name');
} catch(\Exception $e) {
    echo $e->getMessage();
}
```