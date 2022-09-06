<?php

$pdo = new PDO("sqlite:blog.sqlite");

$pdo->exec("CREATE TABLE users (
    uuid VARCHAR(255) PRIMARY KEY NOT NULL,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    login VARCHAR(255) NOT NULL UNIQUE
)");

$pdo->exec("CREATE TABLE posts (
    uuid VARCHAR(255) PRIMARY KEY NOT NULL,
    author_uuid VARCHAR(255) NOT NULL,
    title TEXT NOT NULL,
    text TEXT NOT NULL
)");

$pdo->exec("CREATE TABLE comments (
    uuid VARCHAR(255) PRIMARY KEY NOT NULL,
    post_uuid VARCHAR(255) NOT NULL,
    author_uuid VARCHAR(255) NOT NULL,
    text TEXT NOT NULL
)");