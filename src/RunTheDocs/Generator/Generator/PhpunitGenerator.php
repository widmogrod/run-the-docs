<?php
namespace RunTheDocs\Generator\Generator;

use RunTheDocs\DTO;
use RunTheDocs\Generator\Generator;
use RunTheDocs\Generator\ValueObject;

class BufferedList
{
    /**
     * @var int
     */
    private $limit;
    /**
     * @var int
     */
    private $count;
    /**
     * @var array
     */
    private $data;

    public function __construct(int $limit)
    {
        $this->data = [];
        $this->count = 0;
        $this->limit = $limit;
    }

    public function append($value) : BufferedList
    {
        if ($this->count >= $this->limit) {
            $this->count--;
            $data = $this->data;
            array_shift($data);
            $this->data = $data;
        }

        $this->data[] = $value;
        $this->count++;

        return $this;
    }

    public function map(callable $fn)
    {
        return array_map($fn, $this->data);
    }
}

class PhpunitGenerator implements Generator
{
    public function generate(ValueObject\File $file): DTO\GroupOfExamples
    {
        $tokens = token_get_all($file->getContents(), TOKEN_PARSE);
//        foreach ($tokens as $token) {
//            if (is_array($token)) {
//                list($type, $value, $line) = $token;
//                echo "Line {$line}: ", token_name($type), " ('{$value}')", PHP_EOL;
//            } else {
//                echo "Token $token", PHP_EOL;
//            }
//        }

        $classWithDescription = $this->match(
            [T_DOC_COMMENT, T_WHITESPACE, T_CLASS, T_WHITESPACE, T_STRING],
            function ($values) {
                list($description, , , , $title) = $values;

                return [
                    'id' => $title,
                    'title' => $title,
                    'description' => $description,
                ];
            }
        );

        $methodWithDescription = $this->match(
            [T_DOC_COMMENT, T_WHITESPACE, T_PUBLIC, T_WHITESPACE, T_FUNCTION, T_WHITESPACE, T_STRING],
            function ($values) {
                list($description, , , , , , $title) = $values;

                return [
                    'id' => $title,
                    'title' => $title,
                    'description' => $description,
                    'code' => '',
                ];
            }
        );

        $c = $classWithDescription($tokens);
        var_dump($c['result']);
        $m = $methodWithDescription($c['tokens']);
        var_dump($m['result']);

        return new DTO\GroupOfExamples(
            $c['result']['id'],
            $c['result']['title'],
            $c['result']['description'],
            [
                new DTO\Example(
                    $m['result']['id'],
                    $m['result']['title'],
                    $m['result']['description'],
                    $m['result']['code']
                )
            ]
        );
    }

    private function match(array $pattern, callable $map)
    {
        return function ($tokens) use ($pattern, $map) {
            $list = new BufferedList(count($pattern));
            foreach ($tokens as $index => $token) {
                if (is_array($token)) {
                    $list = $list->append($token);
                    $sequence = $list->map(function ($token) {
                        return $token[0];
                    });

                    if ($sequence === $pattern) {
                        return [
                            'tokens' => array_slice($tokens, $index),
                            'result' => $map($list->map(function ($token) {
                                return $token[1];
                            }))
                        ];
                    }
                }
            }

            return [
                'tokens' => $tokens,
            ];
        };
    }
}
