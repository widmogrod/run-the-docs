<?php
namespace RunTheDocs\Runner\ValueObject;

class ExampleID
{
    private $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    public function asString()
    {
        return $this->value;
    }

    private function validate($value)
    {
        if (!preg_match('/^[\wd_\-]+$/i', $value)) {
            throw Exception\InvalidValueException::notAlfanum($value);
        }
    }
}
