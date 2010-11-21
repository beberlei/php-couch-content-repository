<?php

namespace PHPContentRepository\Tests;

use PHPContentRepository\Commit;
use PHPContentRepository\ContentNode;
use PHPContentRepository\Branch;
use PHPContentRepository\Repository;

class BranchTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $branch = new Branch("Test");
        $this->assertEquals("Test", $branch->getName());
    }

    public function testParent()
    {
        $parent = new Branch("Test");
        $branch = $parent->create("Child");
        $this->assertEquals($parent, $branch->getParent());
    }

    public function testAddCommit()
    {
        $commitUUID = 2;
        $path = "/foo.txt";
        $node = new ContentNode("1", $path);
        $commit = new Commit(Commit::TYPE_ADD, $commitUUID, "eberlei", "foo");

        $branch = new Branch("Test");
        $branch->addCommit($node, $commit);

        $this->assertEquals($commitUUID, $branch->getCommitFor($path));
        $this->assertEquals($commitUUID, $branch->getHead());
    }

    public function testAddCommitsAtOnePath()
    {
        $path = "/foo.txt";
        $node = new ContentNode("1", $path);
        
        $commit1 = new Commit(Commit::TYPE_ADD, "2", "eberlei", "foo");
        $commit2 = new Commit(Commit::TYPE_ADD, "3", "eberlei", "foo");
        $commit3 = new Commit(Commit::TYPE_ADD, "4", "eberlei", "foo");

        $branch = new Branch("Test");
        $branch->addCommit($node, $commit1);
        $branch->addCommit($node, $commit2);
        $branch->addCommit($node, $commit3);

        $this->assertEquals("4", $branch->getCommitFor($path));
        $this->assertEquals("4", $branch->getHead());
    }

    public function testAddCommitsAtMultiplePaths()
    {
        $path1 = "/foo.txt";
        $node1 = new ContentNode("1", $path1);
        $path2 = "/bar.txt";
        $node2 = new ContentNode("1", $path2);

        $commit1 = new Commit(Commit::TYPE_ADD, "2", "eberlei", "foo");
        $commit2 = new Commit(Commit::TYPE_ADD, "3", "eberlei", "foo");
        $commit3 = new Commit(Commit::TYPE_ADD, "4", "eberlei", "foo");

        $branch = new Branch("Test");
        $branch->addCommit($node1, $commit1);
        $branch->addCommit($node2, $commit2);

        $this->assertEquals("2", $branch->getCommitFor($path1));
        $this->assertEquals("3", $branch->getCommitFor($path2));
        $this->assertEquals("3", $branch->getHead());

        $branch->addCommit($node1, $commit3);

        $this->assertEquals("4", $branch->getCommitFor($path1));
        $this->assertEquals("3", $branch->getCommitFor($path2));
        $this->assertEquals("4", $branch->getHead());
    }

    public function testMarkDeleted()
    {
        $branch = new Branch("Test");

        $this->assertFalse($branch->isDeleted());
        $branch->markDeleted();
        $this->assertTrue($branch->isDeleted());
    }

    public function testMarkDeletedModifyDeletedBranch()
    {
        $branch = new Branch("Test");
        $branch->markDeleted();

        $this->setExpectedException("PHPContentRepository\ContentException", "Cannot modify deleted branch 'Test'.");
        $branch->markDeleted();
    }

    public function testAddCommitDeletedBranch()
    {
        $branch = new Branch("Test");
        $branch->markDeleted();

        $this->setExpectedException("PHPContentRepository\ContentException", "Cannot modify deleted branch 'Test'.");
        $branch->addCommit(new ContentNode("1", "/foo.txt"), new Commit(Commit::TYPE_ADD, 2, "eberlei", "foo"));
    }

    public function testCreateSubbranchDeletedBranch()
    {
        $branch = new Branch("Test");
        $branch->markDeleted();

        $this->setExpectedException("PHPContentRepository\ContentException", "Cannot modify deleted branch 'Test'.");
        $branch->create("Child");
    }

    public function testNoCommitAtPath()
    {
        $branch = new Branch("Test");

        $this->setExpectedException("PHPContentRepository\ContentException", "Branch 'Test' doesn't contain content at path '/foo.txt'.");
        $branch->getCommitFor('/foo.txt');
    }

    public function testNoCommitAtSubpathOfExistingPath()
    {
        $branch = new Branch("Test");
        $branch->addCommit(new ContentNode("1", "/foo/bar/baz.txt"), new Commit(Commit::TYPE_ADD, 2, "eberlei", "foo"));

        $this->setExpectedException("PHPContentRepository\ContentException", "Branch 'Test' doesn't contain content at path '/foo/bar'.");
        $branch->getCommitFor('/foo/bar');
    }

    public function testAddCommitAtExistingSubPathFolder()
    {
        $branch = new Branch("Test");
        $branch->addCommit(new ContentNode("1", "/foo/bar/baz.txt"), new Commit(Commit::TYPE_ADD, 2, "eberlei", "foo"));

        $this->setExpectedException("PHPContentRepository\ContentException", "Cannot add content with a folders name '/foo/bar' that already contains other content.");
        $branch->addCommit(new ContentNode("1", "/foo/bar"), new Commit(Commit::TYPE_ADD, 2, "eberlei", "foo"));
    }

    public function testGetCommitHistory()
    {
        $node = new ContentNode("1", "/foo.txt");

        $branch = new Branch("Test");
        $branch->addCommit($node, new Commit(Commit::TYPE_ADD, "2", "eberlei", "foo"));
        $branch->addCommit($node, new Commit(Commit::TYPE_ADD, "3", "eberlei", "foo"));
        $branch->addCommit($node, new Commit(Commit::TYPE_ADD, "4", "eberlei", "foo"));

        $this->assertEquals(array(2, 3, 4), $branch->getCommitHistory());
        $this->assertEquals(array(3, 4), $branch->getCommitHistory(3));
        $this->assertEquals(array(2, 3), $branch->getCommitHistory(null, 3));
    }
}