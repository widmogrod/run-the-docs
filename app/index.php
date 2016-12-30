<?php
require_once __DIR__ . '/../vendor/autoload.php';

//$extractor = new \RunTheDocsPHPUnit\PhpunitExtractor();
//$dto = $extractor->extract(new \RunTheDocs\Extractor\ValueObject\File(
//    __DIR__ . '/../example/ExampleOfEitherMonadTest.php'
//));

$dto = new RunTheDocs\DTO\GroupOfExamples(
    'ExampleOfEitherMonadTest',
    'ExampleOfEitherMonadTest',
    'In php world, the most popular way of saying that something went wrong is to throw an exception.
This results in nasty try catch blocks and many of if statements.

Either Monad shows how we can fail gracefully without breaking the execution chain and making the code more readable.',
    [
        new \RunTheDocs\DTO\Example(
            'test_example_how_array_map_can_be_used',
            'test_example_how_array_map_can_be_used',
            'The following example demonstrates combining the contents of two files into one.
If one of those files does not exist the operation fails gracefully.',
            '
        $read = function ($file) {
            return is_file($file)
                ? Either\Right::of(file_get_contents($file))
                : Either\Left::of(sprintf(\'File "%s" does not exists\', $file));
        };

        $concat = f\liftM2(
            $read(__FILE__),
            $read(\'./this-file-does-not-exits\'),
            function ($first, $second) {
                return $first . $second;
            }
        );

        assert($concat instanceof Either\Left);
        assert($concat->extract() === \'File "./this-file-does-not-exits" does not exists\');
    '
        )
    ]

);

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
