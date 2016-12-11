<?php
namespace RunTheDocs\Runner\ValueObject;

class ExampleID
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
