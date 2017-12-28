<?php
namespace RunTheDocs\DTO;

// type Docs a = Example a | GroupOfExamples a (Docs a)
class Example
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
    private $code;
    /**
     * @var string
     */
    private $description;

    /**
     * @param string $id
     * @param string $title
     * @param string $description
     * @param string $code
     */
    public function __construct(
        string $id,
        string $title,
        string $description,
        string $code
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->code = $code;
        $this->description = $description;
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
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }
}
