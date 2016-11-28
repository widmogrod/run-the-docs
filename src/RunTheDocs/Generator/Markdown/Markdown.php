<?php
namespace RunTheDocs\Generator\Markdown;

use RunTheDocs\DTO;
use RunTheDocs\Generator\Generator;

class Markdown implements Generator
{
    public function generate(DTO\GroupOfExamples $group): string
    {
        $result = $this->introduction($group);
        $result = $this->examples($group->getExamples(), $result);
        return $result;
    }

    private function introduction(DTO\GroupOfExamples $example): string
    {
        return <<<MD
# {$example->getTitle()}
{$example->getDescription()}

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

## {$example->getTitle()}
{$example->getDescription()}

```php
{$example->getCode()}
```

MD;

    }
}
