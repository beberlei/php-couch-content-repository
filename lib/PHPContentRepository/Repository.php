<?php

namespace PHPContentRepository;

class Repository
{
    /**
     * @var string
     */
    private $user;

    /**
     * @var Backend
     */
    private $backend;

    /**
     * @param string $user
     * @param Backend $backend
     */
    public function __construct($user, Backend $backend)
    {
        $this->user = $user;
        $this->backend = $backend;
    }

    /**
     * @return Session
     */
    public function init()
    {
        $branch = new Branch("master");
        $this->backend->saveBranch($branch);

        return $this->createSession("master");
    }

    /**
     * @param  string $branchName
     * @return Session
     */
    public function createSession($branchName)
    {
        return new Session($this->user, $branchName, $this->backend);
    }

    /**
     * @param string $branchName
     * @return void
     */
    public function removeBranch($branchName)
    {
        $branch = $this->backend->getBranch($branchName);
        $branch->markDeleted();

        $this->backend->saveBranch($branch);
    }
}