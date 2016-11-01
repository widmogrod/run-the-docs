<?php
namespace RunTheDocs\Generator\Generator;

use RunTheDocs\DTO;
use RunTheDocs\Generator\Generator;
use RunTheDocs\Generator\ValueObject;

class PhpunitGenerator implements Generator
{
    public function generate(ValueObject\File $file): DTO\GroupOfExamples
    {
        $tokens = token_get_all($file->getContents(), TOKEN_PARSE);
        foreach ($tokens as $token) {
            if (is_array($token)) {
                list($type, $value, $line) = $token;
                echo "Line {$line}: ", token_name($type), " ('{$value}')", PHP_EOL;
            }
        }

        return new DTO\GroupOfExamples(
            $file->getRealPath(),
            'asd',
            'desc',
            []
        );
    }
}
