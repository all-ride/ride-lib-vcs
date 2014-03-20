<?php

namespace ride\library\vcs\git;

use ride\library\vcs\CommitLog;

/**
 * Data container for a commit log
 */
class GitCommitLog extends CommitLog {

    /**
     * Gets the revision in a friendly format
     * @return string
     */
    public function getFriendlyRevision() {
        return substr($this->revision, 0, 7);
    }

}
