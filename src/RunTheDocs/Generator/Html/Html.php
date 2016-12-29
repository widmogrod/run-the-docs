<?php
namespace RunTheDocs\Generator\Html;

use RunTheDocs\DTO;
use RunTheDocs\Generator\Generator;

class Html implements Generator
{
    public function generate(DTO\GroupOfExamples $group): string
    {
        $result = $this->introduction($group);
        $result = $this->examples($group->getExamples(), $result);
        $result = $this->wrapInHtml($group, $result);

        return $result;
    }

    private function introduction(DTO\GroupOfExamples $example): string
    {
        return <<<MD
<h1>{$example->getTitle()}</h1>
<p>{$example->getDescription()}</p>

MD;
    }

    private function examples(array $examples, string $result): string
    {
        return array_reduce($examples, [$this, 'concatExample'], $result);
    }

    private function concatExample(string $result, DTO\Example $example)
    {
        return <<<MD
{$result}

<h2> {$example->getTitle()}</h2>
<p>{$example->getDescription()}</p>

<pre>
{$example->getCode()}
</pre>
<button data-id="{$example->getId()}" class="run-example">run example</button>
<div id="example-output-{$example->getId()}"><div>

MD;

    }

    private function wrapInHtml(DTO\GroupOfExamples $group, string $result): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>{$group->getTitle()}</title>
</head>

<body>
{$result}
</body>

</html>
HTML;
    }
}
