<?php

namespace ride\library\vcs;

use ride\library\system\file\File;

/**
 * Interface for a repository of a version control system
 */
interface Repository {

    /**
     * Sets the remote location of the repository
     * @param string $url Url to the repository
     * @return null
     */
    public function setUrl($url);

    /**
     * Gets the remote location of the repository
     * @return string URL to the repository
     */
    public function getUrl();

    /**
     * Sets the local directory of the repository
     * @param \ride\library\system\file\File $file
     * @return null
     */
    public function setWorkingCopy(File $file);

    /**
     * Gets the local directory of the repository
     * @return \ride\library\system\file\File
     */
    public function getWorkingCopy();

    /**
     * Checks if the repository is initialized
     * @return boolean
     */
    public function isCreated();

    /**
     * Creates a new repository
     * @return null
     */
    public function create();

    /**
     * Performs the initial checkout of the repository to the working copy
     * @param array $options
     * @return null
     */
    public function checkout(array $options = null);

    /**
     * Merges the last changes of the repository to the working copy
     * @return null
     */
    public function update();

    /**
     * Gets the current branch
     * @return string Name of the current branch
     */
    public function getBranch();

    /**
     * Gets the branch names of this repository
     * @return array
     */
    public function getBranches();

    /**
     * Checks if a branch exists
     * @param string $branch Name of the branch
     * @return boolean
     */
    public function hasBranch($branch);

    /**
     * Creates a new branch
     * @param string $branch Name of the branch
     * @return null
     */
    public function createBranch($branch);

    /**
     * Gets the current revision
     * @return string
     */
    public function getRevision();

    /**
     * Gets a specific commit
     * @param string $revision
     * @return \ride\library\vcs\CommitLog|null
     */
    public function getCommit($revision);

    /**
     * Gets the commit messages
     * @param string $path Object path
     * @param integer $number Number of commits to fetch
     * @param string $since Id of a commit
     * @param string $until Id of a commit
     * @return array Array with CommitLog instances
     * @see \ride\library\vcs\CommitLog
     */
    public function getCommits($path = null, $number = null, $since = null, $until = null);

    /**
     * Commits the current working copy to the repository
     * @param string $description Description of the commit
     * @return null
     */
    public function commit($description);

    /**
     * Adds files to the repository
     * @param string|array $files
     * @return null
     */
    public function add($files = null);

    /**
     * Removes files from the repository
     * @param string|array $files
     * @param boolean $recursive
     * @return null
     */
    public function remove($files, $recursive = true);

}
