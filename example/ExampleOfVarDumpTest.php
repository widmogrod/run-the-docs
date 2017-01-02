<?php

use function Widmogrod\Functional\liftM2;
use Widmogrod\Monad\Either;

/**
 * This example set aims to teach you how `var` dump represents different values
 */
class ExampleOfVarDumpTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Given example demonstrates how `var_dump` result will looks like.
     */
    public function test_var_dump()
    {
        var_dump([1, 2, 3]);
    }
}
