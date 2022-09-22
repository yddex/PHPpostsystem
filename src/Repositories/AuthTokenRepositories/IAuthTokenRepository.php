<?php
namespace Maxim\Postsystem\Repositories\AuthTokenRepositories;


use Maxim\Postsystem\Blog\AuthToken;

interface IAuthTokenRepository
{
    public function save(AuthToken $token) :void;
    public function get(string $token) :AuthToken;
}