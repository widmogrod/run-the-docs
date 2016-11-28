<?php
namespace RunTheDocs\Extractor;

use RunTheDocs\DTO;

interface Extractor
{
    public function extract(ValueObject\File $file): DTO\GroupOfExamples;
}
