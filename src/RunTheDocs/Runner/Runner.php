<?php
namespace RunTheDocs\Runner;

interface Runner
{
    public function run(ValueObject\GroupID $group, ValueObject\ExampleID $example): ValueObject\Result;
}
