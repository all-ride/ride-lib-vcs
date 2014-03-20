<?php

namespace ride\library\vcs;

/**
 * Data container for a commit log
 */
class CommitLog {

    /**
     * Revision of the commit
     * @var string
     */
    public $revision;

    /**
     * Author of the commit
     * @var string
     */
    public $author;

    /**
     * Date of the commit
     * @var string
     */
    public $date;

    /**
     * Commit message
     * @var string
     */
    public $message;

    /**
     * Affected files of this commit
     * @var array
     */
    public $files = array();

}
