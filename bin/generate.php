<?php
require_once __DIR__ . '/../vendor/autoload.php';

$generator = new \RunTheDocs\Generator\Generator\PhpunitGenerator();
$result = $generator->generate(new \RunTheDocs\Generator\ValueObject\File(
    __DIR__ . '/../example/test.php'
));
