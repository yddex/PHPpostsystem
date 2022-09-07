<?php
namespace Maxim\Postsystem\Exceptions\Container;

use Maxim\Postsystem\Exceptions\AppException;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends AppException implements NotFoundExceptionInterface{}