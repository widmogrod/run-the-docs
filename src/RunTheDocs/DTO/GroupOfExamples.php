<?php
namespace RunTheDocs\DTO;

class GroupOfExamples
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $description;
    /**
     * @var array|Example[]
     */
    private $examples;

    /**
     * @param string $id
     * @param string $title
     * @param string $description
     * @param Example[] $examples
     */
    public function __construct(
        string $id,
        string $title,
        string $description,
        array $examples
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->examples = $examples;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return array|Example[]
     */
    public function getExamples()
    {
        return $this->examples;
    }
}
