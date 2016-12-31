<?php

use function Widmogrod\Functional\liftM2;
use Widmogrod\Monad\Either;

/**
 * In php world, the most popular way of saying that something went wrong is to throw an exception.
 * This results in nasty try catch blocks and many of if statements.
 *
 * Either Monad shows how we can fail gracefully without breaking the execution chain and making the code more readable.
 */
class ExampleOfEitherMonadTest extends \PHPUnit\Framework\TestCase
{
    /**
     * The following example demonstrates combining the contents of two files into one.
     * If one of those files does not exist the operation fails gracefully.
     */
    public function test_example_how_array_map_can_be_used()
    {
        // $read :: String -> Either String String
        $read = function ($file) {
            return is_file($file)
                ? Either\Right::of(file_get_contents($file))
                : Either\Left::of(sprintf('File "%s" does not exists', $file));
        };

        // $concat :: (Either String String) (Either String String) (Either String String)
        $concat = liftM2(
            function ($first, $second) {
                return $first . $second;
            },
            $read(__FILE__),
            $read('./this-file-does-not-exits')
        );

        assert($concat instanceof Either\Left);
        assert($concat->extract() === 'File "./this-file-does-not-exits" does not exists');
    }
}
