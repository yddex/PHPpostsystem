<?php
namespace Maxim\Postsystem\Http\Actions;

use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\Response;

interface IAction
{
    public function handle(Request $request) : Response;
}