<?php
namespace Maxim\Postsystem\Http;

class ErrorResponse extends Response
{
    protected const SUCCESS = false;
    private string $reason;

    public function __construct(string $reason = "Error response")
    {
        $this->reason = $reason;
    }

    protected function payload(): array
    {
        return ["reason" => $this->reason];
    }
}