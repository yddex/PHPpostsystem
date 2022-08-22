<?php

require_once __DIR__ . "/vendor/autoload.php";

use Maxim\Postsystem\Blog\Post;
use Maxim\Postsystem\MyFaker\MyFaker;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\Person\User;

// spl_autoload_register(function ($class){

//     $file = str_replace("\\", DIRECTORY_SEPARATOR, $class) . ".php";
//     //удаление вендора из пути
//     $file = substr_replace($file, "", 0, strpos($file, DIRECTORY_SEPARATOR) + 1);
//     //замена названия проекта на папку с исходниками
//     $file = substr_replace($file, "src", 0, strpos($file, DIRECTORY_SEPARATOR));
//     require_once $file;

// });
try {
    $faker = Faker\Factory::create("ru_RU");
    $myFaker = new MyFaker($faker);
    if (in_array("user", $argv)) {
        echo $myFaker->getUser() . PHP_EOL;
    }

    if (in_array("post", $argv)) {
        echo $myFaker->getPost() . PHP_EOL;
    }

    if (in_array("comment", $argv)) {
        echo $myFaker->getComment() . PHP_EOL;
    }
} catch (Exception $e) {

    echo $e->getMessage();
}
