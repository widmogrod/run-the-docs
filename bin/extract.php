<?php
require_once __DIR__ . '/../vendor/autoload.php';

$extractor = new \RunTheDocsPHPUnit\PhpunitExtractor();
$result = $extractor->extract(new \RunTheDocs\Extractor\ValueObject\File(
    __DIR__ . '/../example/ExampleOfEitherMonadTest.php'
));
