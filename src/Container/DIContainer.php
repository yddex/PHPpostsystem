<?php
namespace Maxim\Postsystem\Container;

use Maxim\Postsystem\Exceptions\Container\NotFoundException;
use Maxim\Postsystem\Repositories\UserRepositories\IUserRepository;
use Psr\Container\ContainerInterface;
use ReflectionClass;

class DIContainer implements ContainerInterface
{
    private array $resolvers = [];

    public function bind(string $type, $resolver)
    {
        $this->resolvers[$type] = $resolver;
    }



    public function get(string $type) :object
    {

        if(array_key_exists($type, $this->resolvers)){

            //получаем тип объекта, который зарезервирован под переданный тип
            $typeToCreate = $this->resolvers[$type];

            //возвращаем, если это предопределенный объект
            if(is_object($typeToCreate)){
                return $typeToCreate;
            }

            return $this->get($typeToCreate);
        }


        if(!class_exists($type)){
            throw new NotFoundException("Cannot resolve type: " . $type);
        }


        //Создаем объект рефлексии для требуемого класса, чтобы изучить 
        //какие зависимости он требует
        $reflectionClass = new ReflectionClass($type);
        $constructor = $reflectionClass->getConstructor();

        if(is_null($constructor)){
            return new $type();
        }

        $parameters = [];
        foreach($constructor->getParameters() as $parameter){
            
            //Получаем тип параметра конструктора
            $parametrType = $parameter->getType()->getName();

            //Получаем объект зависимости из контейнера
            $parameters[] = $this->get($parametrType);
        }

        //получаем объект нужно типа
        return new $type(...$parameters);
        
    }

    public function has(string $type): bool
    {
        try{
            $this->get($type);
        }catch(NotFoundException){
            return false;
        }

        return true;
    }
}