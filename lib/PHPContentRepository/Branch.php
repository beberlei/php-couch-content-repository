<?php

namespace PHPContentRepository;

class Branch
{
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    /** @var string */
    private $name;

    /**
     * UUID of Head of this branch
     *
     * @var string
     */
    private $head;

    /**
     * What commit was the parent branch on when this branch was created.
     * 
     * @var string
     */
    private $backRefCommit;

    /**
     * What branch was the parent of this one?
     *
     * @var Branch
     */
    private $backRefBranch;

    /**
     * Map of all paths to their corresponding last commit name.
     * 
     * @var array
     */
    private $tree = array();

    /**
     * List of all commits in their appearing order.
     *
     * @var array
     */
    private $commitHistory = array();

    /**
     * Local cache of full path to commit names generated from the tree array.
     * 
     * @var array
     */
    private $paths = array();

    /**
     * @var int
     */
    private $status = self::STATUS_ACTIVE;

    public function __construct($name, Branch $parent = null)
    {
        $this->name = $name;
        if ($parent) {
            $this->head = $this->backRefCommit = $parent->getHead();
            $this->backRefBranch = $parent;
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getBackRefCommit()
    {
        return $this->backRefCommit;
    }

    /**
     * @return Branch
     */
    public function getParent()
    {
        return $this->backRefBranch;
    }

    public function getHead()
    {
        return $this->head;
    }

    /**
     * @throws ContentException
     * @param  string $path
     * @return string
     */
    public function getCommitFor($path)
    {
        if (!isset($this->paths[$path])) {
            $parts = explode("/", ltrim($path, "/"));
            $c = count($parts);
            $subtree = $this->tree;
            for ($i = 0; $i < $c; ++$i) {
                if (isset($subtree[$parts[$i]])) {
                    $subtree = $subtree[$parts[$i]];
                } else {
                    throw ContentException::branchDoesNotContainContentAtPath($this->name, $path);
                }
            }

            if (is_array($subtree)) {
                throw ContentException::branchDoesNotContainContentAtPath($this->name, $path);
            }

            $this->paths[$path] = $subtree;
        }
        return $this->paths[$path];
    }

    /**
     *
     * @throws ContentException
     * @param ContentNode $node
     * @param Commit $commit
     * @return void
     */
    public function addCommit(ContentNode $node, Commit $commit)
    {
        if ($this->status == self::STATUS_DELETED) {
            throw ContentException::cannotModifyDeletedBranch($this->name);
        }

        $parts = explode("/", ltrim($node->getPath(), "/"));

        $c = count($parts);
        $subtree = &$this->tree;
        for ($i = 0; $i < $c; ++$i) {
            if (!isset($subtree[$parts[$i]])) {
                $subtree[$parts[$i]] = array();
            }
            
            if ( ($i+1) == $c ) {
                // prevent subtrees from being overwritten by new content that is named after a folder.
                if (is_array($subtree[$parts[$i]]) && count($subtree[$parts[$i]]) > 0) {
                    throw ContentException::cannotAddContentAtFolderContainingContent($node->getPath());
                }

                $subtree[$parts[$i]] = $commit->getUUID();
            } else {
                $subtree = &$subtree[$parts[$i]];
            }
        }

        $this->commitHistory[] = $commit->getUUID();
        $this->head = $commit->getUUID();

        // update cache!
        $this->paths[$node->getPath()] = $commit->getUUID();
    }

    /**
     * Has this branch a commit with the given uuid?
     *
     * @param  string $commitUUID
     * @return bool
     */
    public function hasCommit($commitUUID)
    {
        return (array_search($commitUUID, $this->commitHistory) !== false);
    }

    /**
     * Get a list of all commit ids for a given range or the total range of this branch.
     * 
     * @param  string $sinceUUID
     * @param  string $untilUUID
     * @return array
     */
    public function getCommitHistory($sinceUUID = null, $untilUUID = null)
    {
        if ($sinceUUID || $untilUUID) {
            $offset = 0;
            $num = null;
            if ($sinceUUID) {
                $offset = array_search($sinceUUID, $this->commitHistory);
                if ($offset === false) {
                    throw ContentException::unknownCommit($sinceUUID);
                }
            }
            if ($untilUUID) {
                $num = array_search($untilUUID, $this->commitHistory);
                if ($num === false) {
                    throw ContentException::unknownCommit($untilUUID);
                }
                $num = $num - $offset + 1;
            }

            return array_slice($this->commitHistory, $offset, $num);
        } else {
            return $this->commitHistory;
        }
    }

    /**
     * Create a new branch.
     *
     * @throws ContentException
     * @param  string $name
     * @return Branch
     */
    public function create($name)
    {
        if ($this->status == self::STATUS_DELETED) {
            throw ContentException::cannotModifyDeletedBranch($this->name);
        }

        $branch = new Branch($name, $this);
        $branch->tree = $this->tree;
        $branch->commitHistory = $this->commitHistory;

        return $branch;
    }

    /**
     * @throws ContentException
     * @return void
     */
    public function markDeleted()
    {
        if ($this->status == self::STATUS_DELETED) {
            throw ContentException::cannotModifyDeletedBranch($this->name);
        }

        $this->status = self::STATUS_DELETED;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return ($this->status == self::STATUS_DELETED);
    }
}
