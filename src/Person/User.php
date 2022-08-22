<?php

namespace Maxim\Postsystem\Person;

use DateTimeImmutable;

class User
{
    private int $id;
    private Name $name;
    private DateTimeImmutable $dateCreate;
    private ?string $login;

    public function __construct(int $id, Name $name, DateTimeImmutable $dateCreate, ?string $login = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->dateCreate = $dateCreate;
        $this->login = $login;
    }

    /**
     * Get the value of id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
        return "Пользователь $this->name. Дата создания " .  $this->dateCreate->format($format);
    }

}