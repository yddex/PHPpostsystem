<?php
namespace Maxim\Postsystem\Blog;


use DateTimeImmutable;
use Maxim\Postsystem\Person\Name;
use Maxim\Postsystem\UUID;
use PhpParser\Node\Stmt\Return_;

class User
{  
    private UUID $uuid;
    private Name $name;
    private DateTimeImmutable $dateCreate;
    private string $login;
    private string $hashPassword;

    public function __construct(UUID $uuid, Name $name, string $login, string $hashPassword)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->dateCreate = new DateTimeImmutable();
        $this->login = $login;
        $this->hashPassword = $hashPassword;
    }

    /**
     * create self object with hash password
     * 
     * @param Name $name Name and Surname user
     * @param string $login user login
     * @param string $password not hash password
     * 
     * @return self User obj
     * 
     */
    public static function createFrom(Name $name, string $login, string $password) :self
    {
        $uuid = UUID::random();
        return new self($uuid, $name, $login, self::hash($password, $uuid));
    }
    /**
     * Hash algorithm
     * 
     * @param string not hashed password
     * @param UUID $uuid salt for hash
     * 
     * @return string hashed password
     */
    private static function hash(string $password, UUID $uuid) :string
    {
        return hash('sha256', $password . $uuid);
    }

    /**
     * Validate password
     * 
     * @param string $password 
     * 
     * @return bool
     */

    public function validatePassword(string $password) :bool
    {
        return $this->hashPassword === self::hash($password, $this->uuid);
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



    /**
     * Get the value of hashPassword
     *
     * @return string
     */
    public function getHashPassword(): string
    {
        return $this->hashPassword;
    }
}