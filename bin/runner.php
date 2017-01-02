<?php
require_once __DIR__ . '/../vendor/autoload.php';

$extractor = new \RunTheDocsPHPUnit\PhpunitExtractor();
$dto = $extractor->extract(new \RunTheDocs\Extractor\ValueObject\File(
    __DIR__ . '/../example/ExampleOfEitherMonadTest.php'
));

$runner = new \RunTheDocsPHPUnit\PhpunitRunner(
    './vendor/bin/phpunit'
);

$result = $runner->run(
    new \RunTheDocs\Runner\ValueObject\GroupID('ExampleOfEitherMonadTest'),
    new \RunTheDocs\Runner\ValueObject\ExampleID('test_example_how_array_map_can_be_used')
);

echo $result->asString();
