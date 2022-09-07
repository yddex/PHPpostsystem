<?php
namespace Maxim\Postsystem\UnitTests\Container\ContainerTestClasses;


class SomeClassWithDependencies
{
    private SomeClassWithoutDependencies $one;
    private SomeClassWithParameter $two;
    public function __construct(SomeClassWithoutDependencies $one, SomeClassWithParameter $two)
    {
        $this->one = $one;
        $this->two = $two;
    }

    /**
     * Get the value of one
     *
     * @return SomeClassWithoutDependencies
     */
    public function getOne(): SomeClassWithoutDependencies
    {
        return $this->one;
    }

    /**
     * Get the value of two
     *
     * @return SomeClassWithParameter
     */
    public function getTwo(): SomeClassWithParameter
    {
        return $this->two;
    }
}