<?php
namespace Maxim\Postsystem\Http;

class SuccessfulResponse extends Response
{
    private array $data;
    protected const SUCCESS = true;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    protected function payload(): array
    {
        return ["data" => $this->data];
    }
}