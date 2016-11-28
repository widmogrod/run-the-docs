<?php
require_once __DIR__ . '/../vendor/autoload.php';

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


$generator = new \RunTheDocs\Generator\Markdown\Markdown();
$result = $generator->generate($dto);

echo $result;
