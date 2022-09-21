<?php
namespace Maxim\Postsystem\Http\Auth;

use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Http\Request;

interface IAuthentication
{
    public function user(Request $request) :User;
}