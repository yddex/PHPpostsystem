<?php

namespace Maxim\Postsystem\UnitTests\Container\ContainerTestClasses;

class SomeClassWithParameter
{
    private int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * Get the value of value
     *
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }
}
