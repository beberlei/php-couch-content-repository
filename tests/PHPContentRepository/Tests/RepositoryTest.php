<?php

namespace PHPContentRepository\Tests;

use PHPContentRepository\Commit;
use PHPContentRepository\ContentNode;
use PHPContentRepository\Branch;
use PHPContentRepository\Repository;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testInitRepository()
    {
        $masterBranch = new Branch('master');
        $backend = $this->getMock('PHPContentRepository\Backend');
        $backend->expects($this->at(0))->method('saveBranch')->with($this->equalTo($masterBranch));
        $backend->expects($this->at(1))->method('getBranch')->with($this->equalTo('master'))->will($this->returnValue($masterBranch));

        $repository = new Repository("eberlei", $backend);
        $session = $repository->init();

        $this->assertType('PHPContentRepository\Session', $session);
        $this->assertEquals('master', $session->getCurrentBranchName());
    }

    public function testCreateSession()
    {
        $masterBranch = new Branch('master');
        $backend = $this->getMock('PHPContentRepository\Backend');
        $backend->expects($this->at(0))->method('getBranch')->with($this->equalTo('master'))->will($this->returnValue($masterBranch));

        $repository = new Repository("eberlei", $backend);
        $session = $repository->createSession("master");

        $this->assertType('PHPContentRepository\Session', $session);
        $this->assertEquals('master', $session->getCurrentBranchName());
    }
}