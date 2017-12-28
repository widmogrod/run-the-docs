<?php

namespace RunTheDocs\DTO\Traverser;

use RunTheDocs\DTO;

interface Traverser
{
    public function traverse(DTO\GroupOfExamples $groupOfExamples): DTO\GroupOfExamples;
}