<?php
namespace Maxim\Postsystem\Blog;

use DateTimeImmutable;
use Maxim\Postsystem\UUID;

class AuthToken
{
    private string $token;
    private UUID $userUuid;
    private DateTimeImmutable $expiresOn;

    public function __construct(string $token, UUID $userUuid, DateTimeImmutable $expiresOn)
    {
        $this->token = $token;
        $this->userUuid = $userUuid;
        $this->expiresOn = $expiresOn;
    }

    /**
     * Get the value of token
     *
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Get the value of userUuid
     *
     * @return UUID
     */
    public function getUserUuid(): UUID
    {
        return $this->userUuid;
    }

    /**
     * Get the value of expiresOn
     *
     * @return DateTimeImmutable
     */
    public function getExpiresOn(): DateTimeImmutable
    {
        return $this->expiresOn;
    }

    public static function generate() :string
    {
        return bin2hex(random_bytes(40));
    }
}