<?php
namespace RunTheDocsConsole\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Config
{
    public $type = 'phpunit';
    public $examples = 'example/*Test.php';
    public $output = 'example/';
}

class RunCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new Config();
        $extractor = new \RunTheDocsPHPUnit\PhpunitExtractor();
        $generator = new \RunTheDocs\Generator\Markdown\Markdown();

        foreach (glob($config->examples) as $file) {
            try {
                $vo = new \RunTheDocs\Extractor\ValueObject\File($file);
                $dto = $extractor->extract($vo);
                $output = $generator->generate($dto);
                $name = basename($file) . '.md';
                $path = $config->output . '/' . $name;
                file_put_contents($path, $output);
            } catch (\Throwable $e) {
                throw new \Exception(
                    sprintf('Error processing file %s', $file),
                    1,
                    $e
                );
            }
        }
    }
}
