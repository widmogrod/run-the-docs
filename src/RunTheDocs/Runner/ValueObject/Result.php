<?php
namespace RunTheDocs\Runner\ValueObject;

class Result
{
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function asString()
    {
        return $this->value;
    }
}
