<?php
require_once __DIR__ . '/../vendor/autoload.php';

$runner = new \RunTheDocs\Runner\PhpunitRunner(
    './vendor/bin/phpunit '
);
$result = $runner->run(
    new \RunTheDocs\Runner\ValueObject\GroupID('ExampleOfEitherMonadTest'),
    new \RunTheDocs\Runner\ValueObject\ExampleID(':test_example_how_array_map_can_be_used')
);

var_dump($result);
