<?php

namespace RunTheDocs\DTO\Traverser;

use RunTheDocs\DTO;

class TitleHumanizeDecorator implements Traverser
{
    public function traverse(DTO\GroupOfExamples $groupOfExamples): DTO\GroupOfExamples
    {
        return new DTO\GroupOfExamples(
            $groupOfExamples->getId(),
            $this->humanize($groupOfExamples->getTitle()),
            $groupOfExamples->getDescription(),
            array_map([$this, 'traverseExample'], $groupOfExamples->getExamples())
        );
    }

    public function traverseExample(DTO\Example $example): DTO\Example
    {
        return new DTO\Example(
            $example->getId(),
            $this->humanize($example->getTitle()),
            $example->getDescription(),
            $example->getCode()
        );
    }

    private function humanize(string $string): string
    {
        $string = preg_split('/(?=[A-Z])|_/',$string);
        $string = array_filter($string);
        return ucwords(join(' ', $string));
    }
}