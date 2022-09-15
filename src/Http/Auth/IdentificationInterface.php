<?php
namespace Maxim\Postsystem\Http\Auth;

use Maxim\Postsystem\Blog\User;
use Maxim\Postsystem\Http\Request;

interface IdentificationInterface
{
    public function user(Request $request) :User;
}