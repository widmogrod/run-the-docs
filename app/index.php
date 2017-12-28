<?php
require_once __DIR__ . '/../vendor/autoload.php';

$parser = (new \PhpParser\ParserFactory)->create(\PhpParser\ParserFactory::PREFER_PHP7);
$extractor = new \RunTheDocsPHPUnit\PhpunitAstExtractor($parser);
$dto = $extractor->extract(new \RunTheDocs\Extractor\ValueObject\File(
    __DIR__ . '/../example/ExampleOfVarDumpTest.php'
));

if (isset($_GET['gid']) && isset($_GET['eid'])) {
    // executed
    $runner = new \RunTheDocsPHPUnit\PhpunitRunner(
        realpath(__DIR__ . '/../vendor/bin/phpunit')
        . ' -c ../phpunit.xml.dist'
    );

    $result = $runner->run(
        new \RunTheDocs\Runner\ValueObject\GroupID($_GET['gid']),
        new \RunTheDocs\Runner\ValueObject\ExampleID($_GET['eid'])
    );

    echo $result->asString();

} else {
    // display
    $loader = new Twig_Loader_Filesystem(__DIR__ . '/twig/');
    $twig = new \Twig_Environment($loader);
    $generator = new \RunTheDocs\Generator\Twig\TwigGenerator($twig, 'index.twig');
    echo $generator->generate($dto);
}
