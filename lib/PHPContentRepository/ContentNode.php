<?php

namespace PHPContentRepository;

class ContentNode
{
    /**
     * @var int
     */
    private $uuid;

    /**
     * @var string
     */
    private $path;

    /**
     * A map of the branch to the last ref commit into this branch.
     *
     * @var array
     */
    private $branches = array();

    /**
     * @var string[]
     */
    private $tags = array();

    /**
     * @var Commit[]
     */
    private $commits = array();

    /**
     * @var DateTime
     */
    private $updated;

    /**
     * @var DateTime
     */
    private $created;

    /**
     * @var Backend
     */
    private $backend;

    /**
     * @param string $path
     */
    public function __construct($uuid, $path)
    {
        $this->assertValidPath($path);

        $this->uuid = $uuid;
        $this->path = $path;
        $this->created = new \DateTime("now");
        $this->updated = new \DateTime("now");
    }

    private function assertValidPath($path)
    {
        if (!preg_match('(^((\/[a-zA-Z0-9:._-]+)+)$)', $path)) {
            throw ContentException::invalidContentPath($path);
        }
    }

    public function setBackend(Backend $backend)
    {
        $this->backend = $backend;
    }

    /**
     * @return int
     */
    public function getUUID()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param Branch $branch
     * @return Commit
     */
    public function getCommit($commitId)
    {
        if (!isset($this->commits[$commitId])) {
            throw ContentException::unknownCommit($commitId);
        }
        return $this->commits[$commitId];
    }

    /**
     * @param Branch $branch
     * @param object $blob
     * @param string $author
     * @param string $message
     * @param array $tags
     * @return Commit
     */
    public function add(Branch $branch, $blob, $author, $message, array $tags = array())
    {
        if (!$this->isNew()) {
            throw ContentException::cannotAddPathThatAlreadyContainsCommits($this->path);
        }

        return $this->createCommit(Commit::TYPE_ADD, $branch, $author, $message, $blob, $tags);
    }

    public function update(Branch $branch, $blob, $author, $message, array $tags = array())
    {
        $parentUUID = $branch->getCommitFor($this->path);
        return $this->createCommit(Commit::TYPE_UPDATE, $branch, $author, $message, $blob, $tags, $this->commits[$parentUUID]);
    }

    public function delete(Branch $branch, $author, $message)
    {
        return $this->createCommit(Commit::TYPE_DELETE, $branch, $author, $message);
    }

    private function createCommit($type, $branch, $author, $message, $blob = null, array $tags = array(), $parent = null)
    {
        $uuid = $this->backend->generateUUID();
        $commit = new Commit($type, $uuid, $author, $message, $blob, $parent);
        $branch->addCommit($this, $commit);
        $this->commits[$uuid] = $commit;
        $this->branches[$branch->getName()] = $uuid;

        if ($tags) {
            $this->tags = array_unique(array_merge($this->tags, $tags));
        }

        return $commit;
    }

    /**
     * Content at path is new if no commits have been issued.
     *
     * @return bool
     */
    public function isNew()
    {
        return (count($this->commits) == 0);
    }
}