<?php

namespace PHPContentRepository;

class ContentException extends \Exception
{
    /**
     * @param  string $branchName
     * @param  string $path
     * @return ContentException
     */
    static public function branchDoesNotContainContentAtPath($branchName, $path)
    {
        return new self("Branch '" . $branchName . "' doesn't contain content at path '" . $path . "'.");
    }

    static public function cannotAddPathThatAlreadyContainsCommits($path)
    {
        return new self("Cannot add path '" . $path . "' that already contains commits. Use commit to update the paths content.");
    }

    static public function unknownCommit($commitId)
    {
        return new self("Commit '" . $commitId . "' could not be found.");
    }

    static public function cannotModifyDeletedBranch($branchName)
    {
        return new self("Cannot modify deleted branch '" . $branchName . "'.");
    }

    static public function invalidContentPath($path)
    {
        return new self("'" . $path . "' is an invalid path.");
    }

    static public function cannotAddContentAtFolderContainingContent($path)
    {
        return new self("Cannot add content with a folders name '" . $path . "' that already contains other content.");
    }

    static public function optimisticLockingException($path)
    {
        return new self("Optimistic Locking failed for path '" . $path . "'.");
    }
}