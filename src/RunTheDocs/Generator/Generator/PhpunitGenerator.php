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


interface At
{
    public function at(int $position);

    public function count(): int;
}

class ArrayAt implements At
{
    private $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function count():int
    {
        return count($this->items);
    }

    public function at(int $position)
    {
        if (!isset($this->items[$position])) {
            throw new \Exception(sprintf(
                'ArrayAt cannot take item at position %d, max %s',
                $position, $this->count() - 1
            ));
        }

        return $this->items[$position];
    }


}

class AppendedAt implements At
{
    private $items;
    private $value;

    public function __construct(At $items, $value)
    {
        $this->items = $items;
        $this->value = $value;
    }

    public function count():int
    {
        return $this->items->count() + 1;
    }

    public function at(int $position)
    {
        if ($this->count() - 1 === $position) {
            return $this->value;
        } else {
            return $this->items->at($position);
        }
    }
}

interface Listt extends ListEmpty, AsHeadAndTail
{
    public function head();

    public function tail(): Listt;

    public function at(int $position);

    public static function fromArray(array $array): Listt;

    public function append($value): Listt;

    public function concat(Listt $list): Listt;

    public function reduce(callable $function, $value);
}

class AList implements Listt
{
    private $items;
    private $position;
    private $isEmpty;

    public static function fromArray(array $array): Listt
    {
        $array = array_values($array);

        return new static(new ArrayAt($array), 0, empty($array));
    }

    private function __construct(
        At $items,
        int $position,
        bool $isEmpty
    ) {
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
            throw new \Exception('AList cannot take head and tail');
        }

        return [$this->head(), $this->tail()];
    }

    public function head()
    {
        if ($this->isEmpty()) {
            throw new \Exception('AList cannot take head');
        }

        return $this->items->at($this->position);
    }

    public function tail() : Listt
    {
        $next = $this->position + 1;

        return $this->items->count() > $next
            ? new static($this->items, $next, false)
            : new static($this->items, $next, true);
    }

    public function at(int $position)
    {
        $pos = $this->position + $position;

        return $this->items->at($pos);
    }

    public function append($value): Listt
    {
        return new static(
            new AppendedAt($this->items, $value),
            0,
            false
        );
    }

    public function concat(Listt $list): Listt
    {
        return $list->reduce(function (Listt $result, $item) {
            return $result->append($item);
        }, $this);
    }

    public function reduce(callable $function, $value)
    {
        for ($i = $this->position; $i < $this->items->count(); $i++) {
            $value = $function($value, $this->at($i));
        }

        return $value;
    }
}

// --

interface Token
{
}

class TokenClassWithDesc implements Token
{
    private $desc;
    private $name;

    public function __construct(PHPToken $desc, PHPToken $name)
    {
        $this->desc = $desc;
        $this->name = $name;
    }
}

class TokenMethodWithDesc implements Token
{
    private $desc;
    private $name;

    public function __construct(PHPToken $desc, PHPToken $name)
    {
        $this->desc = $desc;
        $this->name = $name;
    }
}

class TokenMethodBody implements Token
{

}

class PHPToken
{
    private $type;
    private $value;

    public static function fromArray(array $token): PHPToken
    {
        list($type, $value) = $token;

        return new self($type, $value);
    }

    public static function fromString(string $token)
    {
        return new self($token, $token);
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
            if (is_array($token)) {
                return PHPToken::fromArray($token);
            } else {
                return PHPToken::fromString($token);
            }
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

//    if (isClassWithDescription($list)) {
//        return classWithDescription($list);
//    }

    return isClassWithDescription2($list)
        ->then(function (MatchList $matchList, PHPTokenList $tokenList) {
            return classWithDescription2($matchList, $tokenList);
        }, function () use ($list) {
            return isMethodWithDescription2($list)
                ->then(function (MatchList $matchList, PHPTokenList $tokenList) {
                    return methodWithDescription2($matchList, $tokenList);
                }, function () use ($list) {
                    return tokenize($list->tail());
                });
        });

//    return tokenize($list->tail());

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

function classWithDescription(PHPTokenList $tokenList): TokenList
{
    return TokenList::fromArray([
        new TokenClassWithDesc(
            $tokenList->at(4),
            $tokenList->at(0)
        )
    ]);
}

function isClassWithDescription2(PHPTokenList $tokenList): MatchResult
{
    return match2(
        PatternList::fromArray(
            [T_DOC_COMMENT, T_WHITESPACE, T_CLASS, T_WHITESPACE, T_STRING]
        ),
        $tokenList,
        MatchList::fromArray([])
    );
}

function classWithDescription2(MatchList $matchList, PHPTokenList $tokenList): TokenList
{
    return TokenList::fromArray([
        new TokenClassWithDesc(
            $matchList->at(4),
            $matchList->at(0)
        )
    ])->concat(tokenize($tokenList));
}

function isMethodWithDescription2(PHPTokenList $tokenList): MatchResult
{
    return match2(
        PatternList::fromArray(
            [T_DOC_COMMENT, T_WHITESPACE, T_PUBLIC, T_WHITESPACE, T_FUNCTION, T_WHITESPACE, T_STRING]
        ),
        $tokenList,
        MatchList::fromArray([])
    );
}

function methodWithDescription2(MatchList $matchList, PHPTokenList $tokenList): TokenList
{
    return TokenList::fromArray([
        new TokenMethodWithDesc(
            $matchList->at(6),
            $matchList->at(0)
        )
    ])->concat(tokenize($tokenList));
}

class PatternList extends AList
{

}


// match [] _ : true
// match _ []: false
// match [p:px] [t:tx] => p == t ? match(px, tx) : false


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

interface MatchResult
{
    public function then(callable $matched, callable $else);
}

class Matched implements MatchResult
{
    private $matchList;
    private $tokenList;

    public function __construct(MatchList $matchList, PHPTokenList $tokenList)
    {
        $this->matchList = $matchList;
        $this->tokenList = $tokenList;
    }

    public function then(callable $matched, callable $else)
    {
        return $matched($this->matchList, $this->tokenList);
    }
}

class Miss implements MatchResult
{
    public function then(callable $matched, callable $else)
    {
        return $else();
    }
}

// match2: [a] -> [b] -> ([b], [b])
// match2 [p:px] [t:tx] [m] :: p == t ? [t:m, tx]
// match2 [p:px] [t:tx] [m] :: p != t ? [t:m, tx]

//function macheAgg():

class MatchList extends AList
{
}


function match2(PatternList $patternList, PHPTokenList $tokenList, MatchList $matchList): MatchResult
{
    if ($patternList->isEmpty()) {
        return new Matched($matchList, $tokenList);
    }

    if ($tokenList->isEmpty()) {
        return new Miss();
    }

    $pattern = $patternList->head();
    $token = $tokenList->head();

    if ($token->match($pattern)) {
        return match2($patternList->tail(), $tokenList->tail(), $matchList->append($token));
    }

    return new Miss();
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
//        foreach ($tokens as $token) {
//            if (is_array($token)) {
//                list($type, $value, $line) = $token;
//                echo "Line {$line}: ", token_name($type), " ('{$value}')", PHP_EOL;
//            } else {
//                echo "Token $token", PHP_EOL;
//            }
//        }
//        die;
//        $tokens = array_filter($tokens, 'is_array');
        $tokenList = PHPTokenList::fromTokenList($tokens);
        $tokenizeResult = tokenize($tokenList);
        var_dump($tokenizeResult, '$tokenizeResult');
        die;

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
