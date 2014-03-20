<?php

namespace ride\library\vcs\git;

/**
 * Data container for a GIT object
 */
class GitObject {

    /**
     * Path of the object
     * @var string
     */
    public $path;

    /**
     * Mode of the object
     * @var string
     */
    public $mode;

    /**
     * Type of the object
     * @var string
     */
    public $type;

    /**
     * Revision of the object
     * @var string
     */
    public $revision;

    /**
     * Size of the object
     * @var string
     */
    public $size;

    /**
     * Gets the name of this object
     * @return string
     */
    public function getName() {
        if (strpos($this->path, '/') === false) {
            return $this->path;
        }

        return substr($this->path, strrpos($this->path, '/') + 1);
    }

    /**
     * Checks if this item is a directory
     * @return boolean
     */
    public function isDirectory() {
        return $this->type == 'tree';
    }

    /**
     * Gets the revision in a friendly format
     * @return string
     */
    public function getFriendlyRevision() {
        return substr($this->revision, 0, 7);
    }

}
