<?php

namespace PHPContentRepository\Backend;

use Doctrine\ODM\CouchDB\CouchDBClient;

class DoctrineCouchDB implements \PHPContentRepository\Backend
{
    /**
     * @var CouchDBClient
     */
    private $client;

    /**
     *
     * @var \Object_Freezer
     */
    private $freezer;

    /**
     * @var array
     */
    private $uuids = array();

    /**
     * @var array
     */
    private $branches = array();

    private $couchIdentifier = array();

    /**
     * @param CouchDBClient $client
     */
    public function __construct(CouchDBClient $client, \Object_Freezer $freezer)
    {
        $this->client = $client;
        $this->freezer = $freezer;
    }

    public function generateUUID()
    {
        if (!$this->uuids) {
            $this->uuids = $this->client->getUuids(20);
        }
        return array_pop($this->uuids);
    }

    /**
     * @param string $name
     */
    public function getBranch($name)
    {
        if (!isset($this->branches[$name])) {
            $docId = "branch-" . $name;
            $docResponse = $this->client->findDocument($docId);

            if ($docResponse->status == 200) {
                $this->branches[$name] = $this->freezer->thaw($docResponse->body['state']);
                $this->couchIdentifier[\spl_object_hash($this->branches[$name])] = array(
                    'id' => $docResponse->body['_id'], 'rev' => $docResponse->body['_rev']
                );
            } else {
                // error!
            }
        }
        return $this->branches[$name];
    }

    public function getContentNode($path)
    {
        $docId = "contentnode-" . $path;

        $docResponse = $this->client->findDocument($docId);
        if ($docResponse->status == 200) {
            $node = $this->freezer->thaw($docResponse->body['state']);
            $this->couchIdentifier[\spl_object_hash($node)] = array(
                '_id' => $docResponse->body['_id'], '_rev' => $docResponse->body['_rev']
            );
            return $node;
        } else {
            // error!
        }
    }

    public function saveBranch(Branch $branch)
    {
        $docId = "branch-" . $branch->getName();

        $data = array();
        if (isset($this->couchIdentifier[\spl_object_hash($branch)])) {
            $data = $this->couchIdentifier[\spl_object_hash($branch)];
        }
        $data['state'] = $this->freezer->freeze($branch);

        $updater = $this->client->createBulkUpdater();
        $updater->updateDocument($data);
        $updater->execute();
    }

    public function saveContentNode(ContentNode $content)
    {

    }
}