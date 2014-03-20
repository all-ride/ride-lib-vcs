<?php

namespace ride\library\vcs\git;

/**
 * Parser for the output of the git log command
 */
class GenericGitLogParser implements GitLogParser {

    /**
     * Parses the output of the git log command in data objects
     * @param array $output
     * @return array
     */
    public function parseGitLog(array $output) {
        $commits = array();
        $commit = new GitCommitLog();

        foreach ($output as $line) {
            if (strpos($line, 'commit ') === 0) {
                if ($commit->revision) {
                    $commit->message = trim($commit->message);
                    $commits[$commit->revision] = $commit;

                    $commit = new GitCommitLog();
                }

                $commit->revision = substr($line, 7);

                continue;
            }

            if (strpos($line, 'Author: ') === 0) {
                $commit->author = substr($line, 8);

                continue;
            }

            if (strpos($line, 'Date: ') === 0) {
                $commit->date = substr($line, 6);

                continue;
            }

            if (strpos($line, 'create mode ') !== false || strpos($line, 'delete mode ') !== false) {
                $line = trim($line);
                list($action, $null, $mode, $path) = explode(' ', $line, 4);

                $file = new GitObject();
                $file->path = $path;
                $file->mode = (integer) substr($mode, 2);
                $file->action = $action;

                $commit->files[$path] = $file;

                continue;
            }

            $commit->message .= $line . "\n";
        }

        if ($commit->revision) {
            $commit->message = trim($commit->message);
            $commits[$commit->revision] = $commit;
        }

        return $commits;
    }

}
