<?php

namespace Maxim\Postsystem\Person;

class Name
{
    private string $name;
    private string $surname;

    public function __construct(string $name, string $surname)
    {
        $this->name = $name;
        $this->surname = $surname;
    }

    public function __toString()
    {
        return $this->name . " " . $this->surname;
    }

    /**
     * Get the value of name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the value of surname
     *
     * @return string
     */
    public function getSurname(): string
    {
        return $this->surname;
    }
}