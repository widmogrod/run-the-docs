<?php
namespace RunTheDocs\Generator;

use RunTheDocs\DTO;

interface Generator
{
    public function generate(DTO\GroupOfExamples $group): string;
}
