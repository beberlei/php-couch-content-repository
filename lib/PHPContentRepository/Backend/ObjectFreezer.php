<?php

namespace PHPContentRepository\Backend;

use PHPContentRepository\Branch;
use PHPContentRepository\ContentNode;
use Object_Freezer;
use Object_Freezer_Storage_CouchDB;
use Object_Freezer_IdGenerator_UUID;

class ObjectFreezer implements \PHPContentRepository\Backend
{
    /**
     * @param string $database
     * @param string $host
     * @param int $port
     * @param bool $lazyLoad
     * @return ObjectFreezer
     */
    static public function create($database, $host = 'localhost', $port = 5984, $lazyLoad = false)
    {
        $freezer = new Object_Freezer(null, null, array('PHPContentRepository\Backend\ObjectFreezer'));
        $storage = new Object_Freezer_Storage_CouchDB($database, $freezer, null, $lazyLoad, $host, $port);

        return new ObjectFreezer($storage);
    }

    /**
     * @var Object_Freezer_Storage_CouchDB
     */
    private $couchStorage;

    private $uuidGenerator;

    public function __construct(Object_Freezer_Storage_CouchDB $couchStorage)
    {
        $this->couchStorage = $couchStorage;
        $this->uuidGenerator = new Object_Freezer_IdGenerator_UUID;
    }

    public function generateUUID()
    {
        return $this->uuidGenerator->getId();
    }

    public function getBranch($name)
    {
        return $this->couchStorage->fetch("branch-" . $name);
    }

    public function getContentNode($path)
    {
        try {
            return $this->couchStorage->fetch("content-" . $path);
        } catch(\RuntimeException $e) {
            return null;
        }
    }

    public function saveBlob($blob)
    {
        unset($blob->__php_object_freezer_uuid);
        unset($blob->__php_object_freezer_hash);
    }

    public function saveBranch(Branch $branch)
    {
        $branch->__php_object_freezer_uuid = "branch-" . $branch->getName();
        $this->couchStorage->store($branch);
    }

    public function saveContentNode(ContentNode $content)
    {
        $content->__php_object_freezer_uuid = "content-" . $content->getPath();
        $this->couchStorage->store($content);
    }
}