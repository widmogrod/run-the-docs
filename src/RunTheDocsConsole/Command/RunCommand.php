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
        $parser = (new \PhpParser\ParserFactory)->create(\PhpParser\ParserFactory::PREFER_PHP7);
        $extractor = new \RunTheDocsPHPUnit\PhpunitAstExtractor($parser);
        $generator = new \RunTheDocs\Generator\Markdown\Markdown();

        $docBlockFactory  = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
        $unDock = new \RunTheDocs\DTO\Traverser\UnDocBlockDecorator($docBlockFactory);
        $humanize = new \RunTheDocs\DTO\Traverser\TitleHumanizeDecorator();

        foreach (glob($config->examples) as $file) {
            try {
                $vo = new \RunTheDocs\Extractor\ValueObject\File($file);
                $dto = $extractor->extract($vo);
                $dto = $unDock->traverse($dto);
                $dto = $humanize->traverse($dto);


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
