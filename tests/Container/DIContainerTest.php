<?php

namespace Maxim\Postsystem\UnitTests\Container;

use Maxim\Postsystem\Container\DIContainer;
use Maxim\Postsystem\Exceptions\Container\NotFoundException;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Maxim\Postsystem\UnitTests\Container\ContainerTestClasses\SomeClassWithDependencies;
use Maxim\Postsystem\UnitTests\Container\ContainerTestClasses\SomeClassWithoutDependencies;
use Maxim\Postsystem\UnitTests\Container\ContainerTestClasses\SomeClassWithParameter;
use Maxim\Postsystem\UnitTests\Container\ContainerTestClasses\SomeRepositoryClassWithContract;
use PHPUnit\Framework\TestCase;

class DIContainerTest extends TestCase
{   


    //Проверяем, что будет успешно возвращен обьект имеющий зависимости
    public function testItResolvesClassWithDependencies() :void
    {
        $container = new DIContainer();
        $container->bind(SomeClassWithParameter::class, new SomeClassWithParameter(3));
        
        //пытаем получить обьект имеющий зависимости
        $object = $container->get(SomeClassWithDependencies::class);

        $this->assertInstanceOf(SomeClassWithDependencies::class, $object);
    }

    //проверка на выброс исключения при передачи несуществующего класса
    public function testItThrowsAnExceptionIfCannotResolveType(): void
    {
        // Создаём объект контейнера
        $container = new DIContainer();
        // Описываем ожидаемое исключение
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(
            'Cannot resolve type: Maxim\Postsystem\UnitTests\Container\SomeClass'
        );
        // Пытаемся получить объект несуществующего класса
        $container->get(SomeClass::class);
    }

    //проверка на возврат преопределенного обьекта
    public function testItReturnsPredefinedObject() :void
    {
        $container = new DIContainer();

        //Создаем правило возврата обьекта класса по его названию или интерфейсу
        $container->bind(SomeClassWithParameter::class, new SomeClassWithParameter(1));

        $object = $container->get(SomeClassWithParameter::class);

        $this->assertInstanceOf(SomeClassWithParameter::class, $object);
        $this->assertSame(1, $object->getValue());
    }

    //проверка на возврат класса реализующий интерфейс
    public function testItResolvesClassByContract() :void
    {
        $container = new DIContainer();

        //Устанавливаем правило возврата класса к интерфейсу
        $container->bind(
            IUserRepository::class,
            SomeRepositoryClassWithContract::class
        );

        $object = $container->get(IUserRepository::class);

        $this->assertInstanceOf(SomeRepositoryClassWithContract::class, $object);
    }

    //Проверка на получние обьекта класса без зависимостей
    public function testItResolvesClassWithoutDependencies(): void
    {
        $container = new DIContainer();
        //пытаемся получить обьект класса без зависимостей
        $object = $container->get(SomeClassWithoutDependencies::class);

        $this->assertInstanceOf(SomeClassWithoutDependencies::class, $object);

    }
}

