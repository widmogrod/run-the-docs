<?php

require_once __DIR__ . '/../vendor/autoload.php';

$parser = (new \PhpParser\ParserFactory)->create(\PhpParser\ParserFactory::PREFER_PHP7);
$extractor = new \RunTheDocsPHPUnit\PhpunitAstExtractor($parser);
$result = $extractor->extract(new \RunTheDocs\Extractor\ValueObject\File(
    __DIR__ . '/../example/ExampleOfEitherMonadTest.php'
));
