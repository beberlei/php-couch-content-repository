<?php

namespace PHPContentRepository;

interface Backend
{
    /**
     * @param string $name
     * @return Branch
     */
    public function getBranch($name);

    /**
     * @param string $path
     * @return ContentNode
     */
    public function getContentNode($path);

    /**
     * @return string
     */
    public function generateUUID();

    /**
     * @param ContentNode $content
     * @return void
     */
    public function saveContentNode(ContentNode $content);

    /**
     * @param Branch $branch
     * @return void
     */
    public function saveBranch(Branch $branch);
}