<?php
namespace RunTheDocs\Generator\Generator;

use RunTheDocs\DTO;
use RunTheDocs\Generator\Generator;
use RunTheDocs\Generator\ValueObject;

interface ListEmpty
{
    public function isEmpty(): bool;
}

interface AsHeadAndTail
{
    public function asHeadAndTail(): array;
}


interface Listt extends ListEmpty, AsHeadAndTail {
    public function head();
    public function tail(): Listt;
    public static function fromArray(array $array): Listt;
}


class AList implements Listt
{
    private $items;
    private $position;
    private $isEmpty;

    public static function fromArray(array $array): Listt
    {
        $array = array_values($array);
        return new static(new \ArrayObject($array), 0, empty($array));
    }

    private function __construct(\ArrayObject $items, int $position, bool $isEmpty)
    {
        $this->items = $items;
        $this->position = $position;
        $this->isEmpty = $isEmpty;
    }

    public function isEmpty(): bool
    {
        return $this->isEmpty;
    }

    public function asHeadAndTail(): array
    {
        if ($this->isEmpty()) {
            throw new \Exception('EmptyList cannot take head and tail');
        }

        return [$this->head(), $this->tail()];
    }

    public function head()
    {
        if ($this->isEmpty()) {
            throw new \Exception('EmptyList cannot take head');
        }

        return $this->items[$this->position];
    }

    public function tail() : Listt
    {
        $next = $this->position + 1;
        return isset($this->items[$next])
            ? new static($this->items, $next, false)
            : new static($this->items, $next, true);
    }
}

// --

interface Token
{
}

class TokenClassWithDesc implements Token
{
}

class TokenMethodWithDesc implements Token
{
}

class TokenMethodBody implements Token
{

}

class PHPToken
{
    private $type;
    private $value;

    public static function fromNative(array $token): PHPToken
    {
        list($type, $value) = $token;

        return new self($type, $value);
    }

    private function __construct($type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function match($type)
    {
        return $this->type === $type;
    }
}

class PHPTokenList extends AList
{
    public static function fromTokenList(array $list): Listt
    {
        return self::fromArray(array_map(function ($token) {
            return PHPToken::fromNative($token);
        }, $list));
    }

    public function head() : PHPToken
    {
        return parent::head();
    }
}

class TokenList extends AList
{

}

function error(string $reason, PHPToken $token): TokenList
{

}

function tokenize(PHPTokenList $list) : TokenList
{
    if ($list->isEmpty()) {
        return TokenList::fromArray([]);
    }

    if (isClassWithDescription($list)) {
        return classWithDescription($list);
    }

    return tokenize($list->tail());
//    elseif (isMethodWithDescription($list)) {
//        return methodWithDescription($list);
//    } elseif (isMethodBody($list)) {
//        return methodBody($list);
//    }


}

function isClassWithDescription(PHPTokenList $tokenList): bool
{
    return match(
        PatternList::fromArray(
            [T_DOC_COMMENT, T_WHITESPACE, T_CLASS, T_WHITESPACE, T_STRING]
        ),
        $tokenList
    );
}

function classWithDescription(PHPTokenList $tokenList): TokenList {

}


class PatternList extends AList
{

}

function match(PatternList $patternList, PHPTokenList $tokenList): bool
{
    if ($patternList->isEmpty()) {
        var_dump('$list->isEmpty()');
        return true;
    }

    if ($tokenList->isEmpty()) {
        var_dump('$tokenList->isEmpty()');
        return false;
    }

    return $tokenList->head()->match($patternList->head())
        ? match($patternList->tail(), $tokenList->tail())
        : false;
}

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
        $tokens = array_filter($tokens, 'is_array');
        $tokenList = PHPTokenList::fromTokenList($tokens);
        $tokenizeResult = tokenize($tokenList);
        var_dump($tokenizeResult, '$tokenizeResult');
        die;
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
