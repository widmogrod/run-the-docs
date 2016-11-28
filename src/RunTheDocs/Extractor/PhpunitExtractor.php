<?php
namespace RunTheDocs\Extractor;

use RunTheDocs\DTO;
use RunTheDocs\Extractor\ValueObject;

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

    public function getDesc():string
    {
        return $this->desc->getValue();
    }

    public function getName(): string
    {
        return $this->name->getValue();
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

    public function getDesc():string
    {
        return $this->desc->getValue();
    }

    public function getName(): string
    {
        return $this->name->getValue();
    }
}

class TokenMethodBody implements Token
{
    private $matchList;

    public function __construct(MatchList $matchList)
    {
        $this->matchList = (string)$matchList->reduce(function (string $result, PHPToken $token) {
            return $result . $token;
        }, '');
    }

    public function __toString()
    {
        return $this->matchList;
    }
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

    public function __toString()
    {
        return $this->getValue();
    }

    public function getValue()
    {
        return $this->value;
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
    public function toString()
    {
        return $this->reduce(function (string $result, Token $token) {
            return $result . sprintf('%s()', get_class($token));
        }, '');
    }
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
                    return isMethodBody($list)
                        ->then(function (MatchList $matchList, PHPTokenList $tokenList) {
                            return methodBody2($matchList, $tokenList);
                        }, function () use ($list) {
                            return tokenize($list->tail());
                        });
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

function isMethodBody(PHPTokenList $tokenList): MatchResult
{
    return matchBetween(
        PatternList::fromArray(['(', ')', T_WHITESPACE, '{']),
//        PatternList::fromArray(['}']),
        $tokenList,
        MatchList::fromArray([])
    );
}

function methodBody2(MatchList $matchList, PHPTokenList $tokenList): TokenList
{
    return TokenList::fromArray([
        new TokenMethodBody(
            $matchList
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

function matchBetween(
    PatternList $startList,
//    PatternList $endList,
    PHPTokenList $tokenList,
    MatchList $matchList
): MatchResult
{
    return match2($startList, $tokenList, $matchList)
        // we have beginning
        ->then(function (MatchList $matchList, PHPTokenList $tokenList) {
            // mach between
            return matchUntil(function (PHPToken $token, MatchList $matchedList) {
                if (!$token->match('}')) {
                    return false;
                }

                $eql = function ($value) {
                    return function ($count, PHPToken $matched) use ($value) {
                        return $value === (string)$matched ? $count + 1 : $count;
                    };
                };

                $open = $matchedList->reduce($eql('{'), 0);
                $close = $matchedList->reduce($eql('}'), 0);

                return $open === $close;

            }, $tokenList, MatchList::fromArray([]));
        }, function () {
            return new Miss();
        });
}

function matchUntil(callable $check, PHPTokenList $tokenList, MatchList $matchList): MatchResult
{
    if ($tokenList->isEmpty()) {
        return new Miss();
    }

    $head = $tokenList->head();
    if ($check($head, $matchList)) {
        return new Matched($matchList, $tokenList->tail());
    } else {
        return matchUntil($check, $tokenList->tail(), $matchList->append($head));
    }
}

// Tree = DocsNode String String Tree
//      | GroupNode Tree Tree
//      | CodeNode String

interface Tree
{

}

class DocsNode implements Tree
{
    private $name;
    private $desc;
    private $tree;

    public function __construct(string $name, string $desc, Tree $tree)
    {
        $this->name = $name;
        $this->desc = $desc;
        $this->tree = $tree;
    }
}

class CodeNode implements Tree
{
    private $body;

    public function __construct(string $body)
    {
        $this->body = $body;
    }
}


class GroupNode implements Tree
{
    private $left;
    private $right;

    public function __construct(Tree $left, Tree $right)
    {
        $this->left = $left;
        $this->right = $right;
    }
}

//new Docs('Class', 'desc',
//    new Group(
//        new Group(
//            new Docs('method1', 'desc', new Code('{}')),
//            new Docs('method2', 'desc', new Code('{}'))
//        ),
//        new Group(
//            new Docs('method3', 'desc', new Code('{}')),
//            new Docs('method4', 'desc', new Code('{}'))
//        )
//    )
//);


class TreeToken
{
    private $tree;
    private $tokenList;

    public function __construct(Tree $tree, TokenList $tokenList)
    {
        $this->tree = $tree;
        $this->tokenList = $tokenList;
    }

    public function getTree(): Tree
    {
        return $this->tree;
    }

    public function getTokenList(): TokenList
    {
        return $this->tokenList;
    }
}

class TokenEnd implements Token
{

}

function lookAhead(TokenList $list): Token
{
    if ($list->isEmpty()) {
        return new TokenEnd();
    }
    return $list->head();
}

function accept(TokenList $list): TokenList
{
//    if ($list->isEmpty()) {
//        throw new \Error('nothing to accept');
//    }
    return $list->tail();
}

function parse(TokenList $tokenList): Tree
{
    $group = group($tokenList);

    $tree = $group->getTree();
    $remainingTokens = $group->getTokenList();

    if (!$remainingTokens->isEmpty()) {
        throw new \Exception('Leftover tokens:' . $remainingTokens->toString());
    }

    return $tree;
}

function doc(TokenList $tokenList): TreeToken
{
    $value = lookAhead($tokenList);
    if ($value instanceof TokenClassWithDesc) {
        $group = doc(accept($tokenList));

        return new TreeToken(
            new DocsNode(
                $value->getName(),
                $value->getDesc(),
                $group->getTree()
            ),
            $group->getTokenList()
        );
    }

    if ($value instanceof TokenMethodWithDesc) {
        $code = code(accept($tokenList));

        return new TreeToken(
            new DocsNode(
                $value->getName(),
                $value->getDesc(),
                $code->getTree()
            ),
            $code->getTokenList()
        );
    }

    throw new \Exception('doc: parse error on token: ' . $tokenList->toString());
}

function group(TokenList $tokenList): TreeToken
{
    $doc = doc($tokenList);
    $tree = $doc->getTree();
    $remainingTokens = $doc->getTokenList();

    $value = lookAhead($remainingTokens);
    if ($value instanceof TokenMethodWithDesc) {
        $doc = group($remainingTokens);

        return new TreeToken(
            new GroupNode(
                $tree,
                $doc->getTree()
            ),
            accept($doc->getTokenList())
        );
    }

    if ($value instanceof TokenEnd) {
        return $doc;
    }

    throw new \Exception('group: parse error on token: ' . $tokenList->toString());
}

function code(TokenList $tokenList): TreeToken
{
    $value = lookAhead($tokenList);
    if ($value instanceof TokenMethodBody) {
        return new TreeToken(
            new CodeNode($value),
            accept($tokenList)
        );
    }

    throw new \Exception('code: parse error on token: ' . $tokenList->toString());
}

class PhpunitExtractor implements Extractor
{
    public function generate(ValueObject\File $file): DTO\GroupOfExamples
    {
        $tokens = token_get_all($file->getContents(), TOKEN_PARSE);
        $tokenList = PHPTokenList::fromTokenList($tokens);
        $tokenizeResult = tokenize($tokenList);
        $result = $tokenizeResult->reduce(function (array $result, Token $token) {
            $result[] = $token;
            return $result;
        }, []);


        $tree = parse(TokenList::fromArray($result));
        var_dump($tree);
        die;

//        var_dump($result, '$tokenizeResult');
//        die;

//        return new DTO\GroupOfExamples(
//            $c['result']['id'],
//            $c['result']['title'],
//            $c['result']['description'],
//            [
//                new DTO\Example(
//                    $m['result']['id'],
//                    $m['result']['title'],
//                    $m['result']['description'],
//                    $m['result']['code']
//                )
//            ]
//        );
    }
}
