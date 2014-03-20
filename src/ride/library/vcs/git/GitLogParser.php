<?php

namespace ride\library\vcs\git;

/**
 * Parser for the output of the git log command
 */
interface GitLogParser {

    /**
     * Parses the output of the git log command in data objects
     * @param array $output
     * @return array
     */
    public function parseGitLog(array $output);

}