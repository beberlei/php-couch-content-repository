<?php

namespace PHPContentRepository\Tests;

use PHPContentRepository\Commit;
use PHPContentRepository\ContentNode;
use PHPContentRepository\Branch;
use PHPContentRepository\Repository;
use PHPContentRepository\Session;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckout()
    {
        $masterBranch = new Branch('master');
        $fooBranch =  new Branch('foo');

        $backend = $this->getMock('PHPContentRepository\Backend');
        $backend->expects($this->at(0))->method('getBranch')->with($this->equalTo('master'))->will($this->returnValue($masterBranch));
        $backend->expects($this->at(1))->method('getBranch')->with($this->equalTo('foo'))->will($this->returnValue($fooBranch));

        $session = new Session("eberlei", "master", $backend);
        $this->assertEquals('master', $session->getCurrentBranchName());

        $session->checkout('foo');

        $this->assertEquals('foo', $session->getCurrentBranchName());
    }

    public function testCreateBranch()
    {
        $masterBranch = new Branch('master');

        $backend = $this->getMock('PHPContentRepository\Backend');
        $backend->expects($this->at(0))->method('getBranch')->with($this->equalTo('master'))->will($this->returnValue($masterBranch));
        $backend->expects($this->at(1))->method('saveBranch')->with($this->isInstanceOf('PHPContentRepository\Branch'));

        $session = new Session("eberlei", "master", $backend);
        $session->createBranch('foo');

        $this->assertEquals('foo', $session->getCurrentBranchName());
    }

    public function testFind()
    {
        $contentUUID = 1;
        $path = "/foo.txt";
        $backend = $this->getMock('PHPContentRepository\Backend');
        $content = new ContentNode($contentUUID, $path);
        $content->setBackend($backend);
        $blob = new \stdClass();
        $masterBranch = new Branch('master');

        $backend->expects($this->at(0))->method('generateUUID')->will($this->returnValue(2));
        $backend->expects($this->at(1))->method('getBranch')->with($this->equalTo('master'))->will($this->returnValue($masterBranch));
        $backend->expects($this->at(2))->method('getContentNode')->with($this->equalTo($path))->will($this->returnValue($content));

        $content->add($masterBranch, $blob, "eberlei", "add foo.txt");

        $session = new Session("eberlei", "master", $backend);
        $this->assertSame($blob, $session->find($path));
    }

    public function testAdd()
    {
        $path = "/foo.txt";
        $masterBranch = new Branch('master');
        $blob = new \stdClass();

        $backend = $this->getMock('PHPContentRepository\Backend');
        $backend->expects($this->at(0))->method('getBranch')->with($this->equalTo('master'))->will($this->returnValue($masterBranch));
        $backend->expects($this->at(1))->method('getContentNode')->with($this->equalTo($path))->will($this->returnValue(null));

        $session = new Session("eberlei", "master", $backend);
        $session->add($path, $blob, "foo message");
    }
}