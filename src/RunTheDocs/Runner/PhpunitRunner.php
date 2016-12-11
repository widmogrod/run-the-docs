<?php
namespace RunTheDocs\Runner;

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
        $cmd = '%s --filter=%s::%s';
        $cmd = sprintf($cmd, $this->bin, $group->asString(), $example->asString());

        exec($cmd, $output);

        $output = implode("\n", $output);

        return new ValueObject\Result($output);
    }
}
