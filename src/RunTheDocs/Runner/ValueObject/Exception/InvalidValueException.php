<?php
namespace RunTheDocs\Runner\ValueObject\Exception;

class InvalidValueException extends Exception
{
    public static function notAlfaNum($value)
    {
        $message = 'Given value "%s" is not alpha-numeric';
        $message = sprintf($message, $value);

        return new self($message);
    }
}
