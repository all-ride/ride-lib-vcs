<?php

namespace ride\library\vcs\git;

use ride\library\log\Log;
use ride\library\vcs\exception\VcsException;
use ride\library\system\System;

use \Exception;

/**
 * Client for GIT commands
 */
class GitClient {

    /**
     * Source for the log messages
     * @var string
     */
    const LOG_SOURCE = 'git';

    /**
     * Instance of the system
     * @var \ride\library\system\System
     */
    protected $system;

    /**
     * Path to the binary of GIT
     * @var string
     */
    protected $binary;

    /**
     * Instance of the log
     * @var \ride\library\log\Log
     */
    protected $log;

    /**
     * Constructs a new GIT client
     * @param \ride\library\system\System $system Instance of the system
     * @return null
     */
    public function __construct(System $system) {
        $this->system = $system;
        $this->binary = 'git';
    }

    /**
     * Sets the path to the Git binary
     * @param string $binary
     * @return null
     */
    public function setBinary($binary) {
        $this->binary = $binary;
    }

    /**
     * Sets the log
     * @param \ride\library\log\Log $log
     * @return null
     */
    public function setLog(Log $log = null) {
        $this->log = $log;
    }

    /**
     * Gets the log
     * @return \ride\library\log\Log
     */
    public function getLog() {
        return $this->log;
    }

    /**
     * Executes a GIT command
     * @param string $command
     * @return array Lines of the output
     * @throws \ride\library\vcs\exception\VcsException when the command was not
     * successful
     */
    public function execute(GitRepository $repository, $command) {
        $executeException = null;

        $workingCopy = $repository->getWorkingCopy();

        $currentDirectory = getcwd();
        chdir($workingCopy->getAbsolutePath());

        try {
            $code = 0;

            $shellCommand = $this->binary . ' ' . $command;

            if (strncmp($command, 'pu', 2) === 0 || strncmp($command, 'fetch', 5) === 0) {
                // push, pull or fetch command
                $shellCommand = array(
                    'export GIT_EDITOR=:',
                    $shellCommand,
                );

                $privateKey = $repository->getPrivateKey();
                if ($privateKey) {
                    $shellCommand = array_merge(array(
                        'export GIT_SSH=' . __DIR__ . '/git-ssh.sh',
                        'export GIT_SSH_KEY=' . $privateKey,
                        'export GIT_SSH_PORT=' . $repository->getPort(),
                    ), $shellCommand);
                }

                $output = $this->system->executeInShell($shellCommand, $code);
            } else {
                $output = $this->system->execute($shellCommand, $code);
            }

            if ($code !== 0) {
                throw new VcsException('Could not execute command: git ' . $command);
            }
        } catch (Exception $exception) {
            $executeException = $exception;
        }

        chdir($currentDirectory);

        if ($executeException) {
            throw $executeException;
        }

        return $output;
    }

}
