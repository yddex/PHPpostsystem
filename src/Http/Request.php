<?php

namespace Maxim\Postsystem\Http;

use JsonException;
use Maxim\Postsystem\Exceptions\Http\HttpException;

class Request
{
    private array $get;
    private array $server;
    private string $body;

    public function __construct(array $get, array $server, string $body)
    {
        $this->get = $get;
        $this->server = $server;
        $this->body = $body;
    }

    //Получение метода запроса
    public function method(): string
    {
        if (!array_key_exists("REQUEST_METHOD", $this->server)) {
            throw new HttpException("Cannot get method from the request");
        }

        return $this->server["REQUEST_METHOD"];
    }

    //Получение пути из URI
    public function path(): string
    {
        if (!array_key_exists("REQUEST_URI", $this->server)) {
            throw new HttpException("Cannot get path from request");
        }

        $components = parse_url($this->server["REQUEST_URI"]);

        if (!is_array($components) || !array_key_exists("path", $components)) {
            throw new HttpException("Cannot get path from request");
        }

        return $components["path"];
    }

    //Получение значения из переданного параметра
    public function query(string $param): string
    {
        if (!array_key_exists($param, $this->get)) {
            throw new HttpException("No such query param in the request: $param");
        }

        $value = $this->get[$param];

        if (empty($value)) {
            throw new HttpException("Empty query param in the request: $param");
        }

        return $value;
    }

    //Получение заголовка из запроса
    public function header(string $header): string
    {
        $headerName = mb_strtoupper("http_" . str_replace("-", "_", $header));

        if (!array_key_exists($headerName, $this->server)) {
            throw new HttpException("No such header in the request: $header");
        }

        $value = trim($this->server[$headerName]);

        if (empty($value)) {
            throw new HttpException("Empty header in the request: $header");
        }

        return $value;
    }

    //тело запроса в виде ассоциативного массива
    public function jsonBody(): array
    {
        try {
            $data = json_decode($this->body, associative: true, flags: JSON_THROW_ON_ERROR);
            
        } catch (JsonException) {
            throw new HttpException("Cannot decode json body");
        }

        if (!is_array($data)) {
            throw new HttpException("Not an array/object in json body");
        }

        return $data;
    }

    //Значение из тела запроса
    public function jsonBodyField(string $field): mixed
    {
        $data = $this->jsonBody();

        if (!array_key_exists($field, $data)) {
            throw new HttpException("No such field: $field");
        }
        if (empty($data[$field])) {
            throw new HttpException("Empty field: $field");
        }

        return $data[$field];
    }
}
