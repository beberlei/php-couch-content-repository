<?php

namespace PHPContentRepository;

class Commit
{
    const TYPE_ADD = 1;
    const TYPE_UPDATE = 2;
    const TYPE_DELETE = 3;
    const TYPE_MERGE = 4;

    /**
     * @var string
     */
    private $uuid;

    /**
     * @var int
     */
    private $type;

    /**
     * @var object
     */
    private $blob;

    /**
     * @var string
     */
    private $author = "";

    /**
     * @var string
     */
    private $message = "";

    /**
     * @var Commit
     */
    private $parent;

    /**
     * @var DateTime
     */
    private $created;

    public function __construct($type, $uuid, $author, $message, $blob = null, $parent = null)
    {
        $this->type = $type;
        $this->uuid = $uuid;
        $this->blob = $blob;
        $this->author = $author;
        $this->message = $message;
        $this->parent = $parent;
        $this->created = new \DateTime("now");
    }

    public function getUUID()
    {
        return $this->uuid;
    }

    /**
     * @return object
     */
    public function getBlob()
    {
        return $this->blob;
    }
}