<?php

namespace Maxim\Postsystem\Person;

use DateTimeImmutable;
use Maxim\Postsystem\UUID;

class User
{
    private UUID $uuid;
    private Name $name;
    private DateTimeImmutable $dateCreate;
    private string $login;

    public function __construct(UUID $uuid, Name $name, string $login)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->dateCreate = new DateTimeImmutable();
        $this->login = $login;
    }


    /**
     * Get the value of uuid
     *
     * @return UUID
     */
    public function getUuid(): UUID
    {
        return $this->uuid;
    }

    /**
     * Get the value of name
     *
     * @return Name
     */
    public function getName(): Name
    {
        return $this->name;
    }


    /**
     * Get the value of login
     *
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * Get the value of dateCreate
     *
     * @return DateTimeImmutable
     */
    public function getDateCreate(): DateTimeImmutable
    {
        return $this->dateCreate;
    }


    public function __toString()
    {
        $format = "d.m.Y";
        return "Пользователь $this->name. UUID: $this->uuid" . PHP_EOL .  "Дата создания: " .  $this->dateCreate->format($format);
    }



}