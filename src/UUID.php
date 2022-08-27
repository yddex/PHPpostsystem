<?php
namespace Maxim\Postsystem;

use InvalidArgumentException;

class UUID
{
    private string $uuid;
    public function __construct(string $uuid)
    {
        if(!uuid_is_valid($uuid)){
            throw new InvalidArgumentException(
                "Malformed UUID: $uuid"
            );
        }

        $this->uuid = $uuid;
    }

    public static function random() :self
    {
        return new self(uuid_create(UUID_TYPE_RANDOM));
    }

    public function __toString()
    {
        return $this->uuid;
    }
}

