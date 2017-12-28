<?php

namespace RunTheDocsPHPUnit;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use RunTheDocs\DTO;
use RunTheDocs\Extractor\Extractor;
use RunTheDocs\Extractor\ValueObject;
use RunTheDocsPHPUnit\PhpParser\ExampleExtractorVisitor;

class PhpunitAstExtractor implements Extractor
{
    /**
     * @var Parser
     */
    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function extract(ValueObject\File $file): DTO\GroupOfExamples
    {
        $ast = $this->parser->parse($file->getContents());

        $visitor = new ExampleExtractorVisitor();

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return $visitor->getResult();
    }
}