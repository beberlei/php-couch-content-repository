<?php

namespace PHPContentRepository\Tests\Functional;

class ObjectFreezerTest extends \PHPUnit_Framework_TestCase
{
    private $storage;
    private $backend;
    private $repository;

    public function setUp()
    {
        $freezer = new \Object_Freezer(null, null, array('PHPContentRepository\Backend\ObjectFreezer'));
        $this->storage = new \Object_Freezer_Storage_CouchDB('phpcr', $freezer, null, false, 'localhost', 5984);
        $this->storage->send('DELETE', '/phpcr');
        $this->storage->send('PUT', '/phpcr');
        $this->backend = new \PHPContentRepository\Backend\ObjectFreezer($this->storage);
        $this->repository = new \PHPContentRepository\Repository("eberlei", $this->backend);
    }

    public function testCRUD()
    {
        $session = $this->repository->init();
        $session->add('/blog/post-1.html', new BlogPost('blub', 'blub'), 'Add Blog Post');

        $session = $this->repository->createSession("master");
        $blogPost = $post = $session->find('/blog/post-1.html');
        $blogPost->setText("lakjsfklasjdf");
        $session->commit('/blog/post-1.html', $blogPost, 'updated', array('foo', 'bar'));

        $session = $this->repository->createSession("master");
        $blogPost = $post = $session->find('/blog/post-1.html');
        $this->assertEquals("lakjsfklasjdf", $blogPost->getText());
    }
}

class BlogPost
{
    private $title;
    private $text;

    public function __construct($title, $text)
    {
        $this->title = $title;
        $this->text = $text;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }
}