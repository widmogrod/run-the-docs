<?php

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

    /**
     * @dataProvider provideData
     */
    public function test_value_is_injected($value)
    {
        var_dump($value);
    }

    /**
     * @dataProvider provideData
     */
    public function test_value_is_injected_second_time($value)
    {
        var_dump($value);
    }

    /**
     */
    public function provideData()
    {
        return [
            'random input' => [
                '$value' => mt_rand(),
            ],
        ];
    }
}
