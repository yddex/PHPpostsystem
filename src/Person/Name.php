<?php

namespace Maxim\Postsystem\Person;

class Name
{
    private string $name;
    private ?string $surname;

    public function __construct(string $name, ?string $surname = null)
    {
        $this->name = $name;
        $this->surname = $surname;
    }

    public function __toString()
    {
        return $this->name . $this->surname ?: " $this->surname";
    }
}