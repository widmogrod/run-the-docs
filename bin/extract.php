<?php
require_once __DIR__ . '/../vendor/autoload.php';

$generator = new \RunTheDocs\Extractor\PhpunitExtractor();
$result = $generator->generate(new \RunTheDocs\Extractor\ValueObject\File(
    __DIR__ . '/../example/test.php'
));
