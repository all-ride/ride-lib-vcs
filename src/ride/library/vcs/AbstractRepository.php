<?php

namespace ride\library\vcs;

use ride\library\system\exception\SystemException;
use ride\library\system\file\File;
use ride\library\vcs\exception\VcsException;

/**
 * Abstract implementation for a repository of a version control system
 */
abstract class AbstractRepository implements Repository {

    /**
     * URL for the remote location
     * @var string
     */
    protected $url;

    /**
     * Directory of the working copy
     * @var \ride\library\system\file\File
     */
    protected $workingCopy;

    /**
     * Sets the remote location of the repository
     * @param string $url URL to the repository
     * @return null
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * Gets the remote location of the repository
     * @return string URL to the repository
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Sets the local directory of the repository
     * @param \ride\library\system\file\File $workingCopy
     * @return null
     */
    public function setWorkingCopy(File $workingCopy) {
        try {
            $workingCopy->create();
        } catch (SystemException $exception) {
            throw new VcsException('Could not set the working directory: ' . $workingCopy . ' does not exist', 0, $exception);
        }

        if (!$workingCopy->isDirectory()) {
            throw new VcsException('Could not set the working directory: ' . $workingCopy . ' is not a directory');
        }

        if (!$workingCopy->isWritable()) {
            throw new VcsException('Could not set the working directory: ' . $workingCopy . ' is not writable');
        }

        $this->workingCopy = $workingCopy;
    }

    /**
     * Gets the local directory of the repository
     * @return \ride\library\system\file\File
     */
    public function getWorkingCopy() {
        return $this->workingCopy;
    }

    /**
     * Checks if a branch exists
     * @param string $branch Name of the branch
     * @return boolean
     */
    public function hasBranch($branch) {
        $branches = $this->getBranches();

        return isset($branches[$branch]);
    }

}
