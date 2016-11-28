<?php
namespace RunTheDocs\Extractor;

use RunTheDocs\DTO;

interface Extractor
{
    public function generate(ValueObject\File $file): DTO\GroupOfExamples;
}
