<?php
namespace RunTheDocsPHPUnit;

use RunTheDocs\Runner\Runner;
use RunTheDocs\Runner\ValueObject;

class PhpunitRunner implements Runner
{
    /**
     * @var string
     */
    private $bin;

    public function __construct(
        string $bin
    ) {
        $this->bin = $bin;
    }

    public function run(ValueObject\GroupID $group, ValueObject\ExampleID $example): ValueObject\Result
    {
        $id = $group->asString() . '::' . $example->asString();
        $cmd = '%s --filter=%s';
        $cmd = sprintf($cmd, escapeshellcmd($this->bin), escapeshellarg($id));

        exec($cmd, $output);

        $output = implode("\n", $output);

        return new ValueObject\Result($output);
    }
}
