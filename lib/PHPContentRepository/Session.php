<?php

namespace PHPContentRepository;

class Session
{
    const MERGE_FF = 0;
    const MERGE_FORCE = 1;

    private $user;

    /**
     * @var Branch
     */
    private $currentBranch;

    /**
     * @var Backend
     */
    private $backend;

    private $paths = array();

    /**
     * @param string $user
     * @param string $branchName
     * @param Backend $backend
     */
    public function __construct($user, $branchName, Backend $backend)
    {
        $this->user = $user;
        $this->backend = $backend;
        $this->checkout($branchName);
    }

    /**
     * @return string
     */
    public function getCurrentBranchName()
    {
        return $this->currentBranch->getName();
    }

    /**
     * Get content for the path.
     * 
     * @param  string $path
     * @return ContentNode
     */
    private function getContentNodeAtPath($path)
    {
        // @todo assert is valid $path
        if (!isset($this->paths[$path])) {
            $this->paths[$path] = $this->backend->getContentNode($path);
            if (!$this->paths[$path]) {
                $this->paths[$path] = new ContentNode($this->backend->generateUUID(), $path);
            }
            $this->paths[$path]->setBackend($this->backend);
        }
        return $this->paths[$path];
    }

    /**
     * Return the blob saved at the given path.
     *
     * @param string $path
     * @return object
     */
    public function find($path)
    {
        $commitId = $this->currentBranch->getCommitFor($path);

        $node = $this->getContentNodeAtPath($path);
        return $node->getCommit($commitId)->getBlob();
    }

    /**
     * @param string $path
     * @param object $blob
     * @param array $tags
     * @return void
     */
    public function add($path, $blob, $message, array $tags = array())
    {
        $node = $this->getContentNodeAtPath($path);
        $node->add($this->currentBranch, $blob, $this->user, $message, $tags);

        $this->backend->saveBlob($blob);
        $this->backend->saveContentNode($node);
        $this->backend->saveBranch($this->currentBranch);
    }

    /**
     * Commit changes to an existing path in the currently active branch.
     * 
     * @param string $path
     * @param object $blob
     * @param string $mesage
     * @param array $tags
     * @return void
     */
    public function commit($path, $blob, $message, array $tags = array())
    {
        $node = $this->getContentNodeAtPath($path);
        $node->update($this->currentBranch, $blob, $this->user, $message, $tags);

        $this->backend->saveBlob($blob);
        $this->backend->saveContentNode($node);
        $this->backend->saveBranch($this->currentBranch);
    }

    /**
     * Delete the contents at the path in the currently active branch.
     * 
     * @param string $path
     * @param string $message
     * @return void
     */
    public function rm($path, $message)
    {
        $node = $this->getContentNodeAtPath($path);
        $node->delete($this->currentBranch, $this->user, $message);

        $this->backend->saveContentNode($node);
        $this->backend->saveBranch($this->currentBranch);
    }

    /**
     * Checkout an existing branch.
     *
     * @param  string $branchName
     * @return void
     */
    public function checkout($branchName)
    {
        $this->currentBranch = $this->backend->getBranch($branchName);
    }

    /**
     * Create a new branch off this one.
     *
     * @param string $name
     * @return void
     */
    public function createBranch($name)
    {
        $this->currentBranch = $this->currentBranch->create($name);
        $this->backend->saveBranch($this->currentBranch);
    }

    public function removeBranch()
    {
        $this->currentBranch->markDeleted();
        $this->backend->saveBranch($this->currentBranch);
    }

    public function listBranches($refBranchName = null)
    {
        throw new \BadMethodCallException();
    }

    /**
     *
     * @param string $path
     * @param array $tags
     */
    public function removeTags($path, array $tags)
    {
        throw new \BadMethodCallException("Removing Tags is not yet supported.");
    }

    public function tree($rootPath = '/')
    {
        throw new \BadMethodCallException("Listing trees and subtrees is not yet possible.");
    }

    public function merge($otherBranch, $path)
    {
        throw new \BadMethodCallException("Merge is not supported yet.");
    }

    public function log($from = null, $to = null)
    {
        throw new \BadMethodCallException();
    }

    public function diff($otherBranch, $path)
    {
        throw new \BadMethodCallException();
    }

    public function revert($path, $commit)
    {
        throw new \BadMethodCallException();
    }
}