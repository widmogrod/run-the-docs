<?php

namespace RunTheDocs\DTO\Traverser;

use phpDocumentor\Reflection\DocBlockFactoryInterface;
use RunTheDocs\DTO;

class UnDocBlockDecorator implements Traverser
{
    /**
     * @var DocBlockFactoryInterface
     */
    private $blockFactory;

    public function __construct(DocBlockFactoryInterface $blockFactory)
    {
        $this->blockFactory = $blockFactory;
    }

    public function traverse(DTO\GroupOfExamples $groupOfExamples): DTO\GroupOfExamples
    {
        return new DTO\GroupOfExamples(
            $groupOfExamples->getId(),
            $groupOfExamples->getTitle(),
            $this->unDoc($groupOfExamples->getDescription()),
            array_map([$this, 'traverseExample'], $groupOfExamples->getExamples())
        );
    }

    public function traverseExample(DTO\Example $example): DTO\Example
    {
        return new DTO\Example(
            $example->getId(),
            $example->getTitle(),
            $this->unDoc($example->getDescription()),
            $example->getCode()
        );
    }

    private function unDoc(string $docBlock): string
    {
        return $this->blockFactory->create($docBlock)->getDescription();
    }
}