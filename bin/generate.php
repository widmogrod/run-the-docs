<?php
require_once __DIR__ . '/../vendor/autoload.php';

$extractor = new \RunTheDocsPHPUnit\PhpunitExtractor();
$dto = $extractor->extract(new \RunTheDocs\Extractor\ValueObject\File(
    __DIR__ . '/../example/ExampleOfEitherMonadTest.php'
));

$generator = new \RunTheDocs\Generator\Markdown\Markdown();
$result = $generator->generate($dto);

echo $result;
