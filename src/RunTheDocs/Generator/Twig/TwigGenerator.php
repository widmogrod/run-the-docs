<?php
namespace RunTheDocs\Generator\Twig;

use RunTheDocs\DTO;
use RunTheDocs\Generator\Generator;

class TwigGenerator implements Generator
{
    /**
     * @var \Twig_Environment
     */
    private $twig;
    /**
     * @var string
     */
    private $templateName;

    public function __construct(
        \Twig_Environment $twig,
        string $templateName
    ) {
        $this->twig = $twig;
        $this->templateName = $templateName;
    }

    public function generate(DTO\GroupOfExamples $group): string
    {
        return $this->twig->render($this->templateName, [
            'groupOfExamples' => $group,
        ]);
    }
}
