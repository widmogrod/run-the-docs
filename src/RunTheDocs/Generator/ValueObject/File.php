<?php
namespace RunTheDocs\Generator\ValueObject;

class File
{
    private $file;

    public function __construct(string $file)
    {
        if (!file_exists($file)) {
            throw new Exception\FileNotExistsException($file);
        }

        if (!is_readable($file)) {
            throw new Exception\FileIsNotReadableException($file);
        }

        $this->file = realpath($file);
    }

    public function getRealPath(): string
    {
        return $this->file;
    }

    public function getContents(): string
    {
        return file_get_contents($this->getRealPath());
    }
}
