<?php
/**
 * Created by PhpStorm.
 * User: gabrielhabryn
 * Date: 28/12/2017
 * Time: 20:47
 */

namespace RunTheDocsPHPUnit\PhpParser;


use PhpParser\Node;
use RunTheDocs\DTO;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter\Standard;

class ExampleExtractorVisitor extends NodeVisitorAbstract
{
    public function __construct()
    {
        $this->examples = new \SplQueue();
        $this->prettyPrinter = new Standard();
        $this->result = new DTO\GroupOfExamples(
            'nothing',
            'nothing',
            'Could not find examples',
            []
        );
    }

    public function getResult(): DTO\GroupOfExamples
    {
        return $this->result;
    }

    private function getComment(Node $node): string
    {
        $result = "";
        $comments = $node->getAttribute('comments');
        if ($comments) {
            foreach ($comments as $comment) {
                if ($comment instanceof \PhpParser\Comment) {
                    $result .= $comment->getText();
                }
            }
        }
        return $result;
    }

    private function getBody(\PhpParser\Node\Stmt\ClassMethod $node)
    {
        return $this->prettyPrinter->prettyPrint($node->getStmts());
    }

    public function enterNode(Node $node)
    {

        // Example
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $this->examples->enqueue(new DTO\Example(
                $node->name,
                $node->name,
                $this->getComment($node),
                $this->getBody($node)
            ));
        }
    }

    public function leaveNode(Node $node)
    {
        // Group of examples
        if ($node instanceof \PhpParser\Node\Stmt\Class_) {
            $this->result = new DTO\GroupOfExamples(
                $node->name,
                $node->name,
                $this->getComment($node),
                iterator_to_array($this->examples)
            );
        }
    }
}