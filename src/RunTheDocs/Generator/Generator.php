<?php
namespace RunTheDocs\Generator;

use RunTheDocs\DTO;

interface Generator
{
    public function generate(ValueObject\File $file): DTO\GroupOfExamples;
}
