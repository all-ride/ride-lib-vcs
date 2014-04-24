<?php

namespace ride\library\vcs\git;

use ride\library\vcs\AbstractRepository;

/**
 * GIT repository
 */
class GitRepository extends AbstractRepository {

    /**
     * Instance of the GIT client
     * @var GitClient
     */
    protected $client;

    /**
     * Instance of the log parser
     * @var GitLogParser
     */
    protected $logParser;

    /**
     * Path to the private key of the user
     * @var string
     */
    protected $privateKey;

    /**
     * Constructs a new repository
     * @param GitClient $git
     * @return null
     */
    public function __construct(GitClient $client, GitLogParser $logParser) {
        $this->client = $client;
        $this->logParser = $logParser;
        $this->privateKey = null;
    }

    /**
     * Gets the port for this connection
     * @return int
     */
    public function getPort() {
        $port = parse_url($this->url, PHP_URL_PORT);
        if ($port) {
            return $port;
        }

        return 22;
    }

    /**
     * Sets the private key to access the repository
     * @param string $privateKey Path to the private key
     * @return null
     */
    public function setPrivateKey($privateKey) {
        $this->privateKey = $privateKey;
    }

    /**
     * Gets the private key to access the repository
     * @return string|null Path to the private key
     */
    public function getPrivateKey() {
        return $this->privateKey;
    }

    /**
     * Executes a command on the repository
     * @param string $command
     * @return array Output of the command
     */
    public function git($command) {
        return $this->client->execute($this, $command);
    }

    /**
     * Checks if the repository is initialized
     * @return boolean
     */
    public function isCreated() {
        return $this->workingCopy && $this->workingCopy->getChild('.git')->exists();
    }

    /**
     * Creates a new repository
     * @return null
     */
    public function create() {
        if (!$this->workingCopy) {
            throw new VcsException('Could not create repository: no working copy set');
        }

        $this->workingCopy->create();

        $this->client->execute($this, 'init');

        if ($this->url) {
            $this->client->execute($this, 'remote add origin ' . $this->url);
        }
    }

    /**
     * Performs a checkout of the repository to the working copy
     * @param array $options (branch, orphan)
     * @return null
     */
    public function checkout(array $options = null) {
        if (isset($options['no-checkout'])) {
            $isNoCheckout = $options['no-checkout'];

            unset($options['no-checkout']);
        } else {
            $isNoCheckout = false;
        }

        if (!$options) {
            $command = 'clone ' . ($isNoCheckout ? '-n ' : '') . $this->url . ' ' . $this->workingCopy->getAbsolutePath();
        } else {
            $branch = isset($options['branch']) ? $options['branch'] : null;
            $isOrphan = isset($options['orphan']) && $options['orphan'];

            $command = 'checkout ' . ($isOrphan ? '--orphan ' : '') . $branch;
        }

        $this->client->execute($this, $command);
    }

    /**
     * Merges the last changes of the repository to the working copy
     * @return null
     */
    public function update(array $options = null) {
        if (isset($options['all']) && $options['all']) {
            $command = 'fetch --all';
        } else {
            $origin = isset($options['origin']) ? $options['origin'] : null;
            $branch = isset($options['branch']) ? $options['branch'] : null;

            $command = trim('pull ' . $origin . ' ' . $branch);
        }

        $this->client->execute($this, $command);
    }

    /**
     * Gets the status of repository
     * @return array
     */
    public function status() {
        return $this->client->execute($this, 'status -s');
    }

    /**
     * Checks whether this repository has changes pending
     * @return boolean
     */
    public function hasChanges() {
        return $this->status() ? true : false;
    }

    /**
     * Gets the current branch
     * @return string Name of the current branch
     */
    public function getBranch() {
        $branch = 'master';

        $output = $this->client->execute($this, 'branch -a');
        foreach ($output as $line) {
            if (substr($line, 0, 1) != '*') {
                continue;
            }

            $branch = substr($line, 2);

            break;
        }

        return $branch;
    }

    /**
     * Gets the branch names of this repository
     * @return array
     */
    public function getBranches() {
        $branches = array();

        $output = $this->client->execute($this, 'branch -a');
        foreach ($output as $line) {
            $branch = trim(str_replace('*', '', $line));

            if (strpos($branch, 'remotes/origin/') === false) {
                continue;
            }

            $branch = str_replace('remotes/origin/', '', $branch);
            if (strpos($branch, '->') !== false) {
                continue;
            }

            $branches[$branch] = $branch;
        }

        return $branches;
    }

    /**
     * Creates a new branch
     * @param string $branch Name of the branch
     * @return null
     */
    public function createBranch($branch) {
        $this->client->execute($this, 'checkout -b ' . $branch);
    }

    /**
     * Checks out a specific revision
     * @param string $revision
     * @return null
     */
    public function checkoutRevision($revision) {
        $this->client->execute($this, 'checkout ' . $revision . ' .');
    }

    /**
     * Resets to a specific revision
     * @param string $revision
     * @return null
     */
    public function reset($revision) {
        $this->client->execute($this, 'reset --hard ' . $revision);
    }

    /**
     * Gets the current revision
     * @return string
     */
    public function getRevision() {
        $output = $this->client->execute($this, 'log -n 1');

        $output = array_shift($output);
        if (strpos($output, 'commit ') === 0) {
            return substr($output, 7);
        }

        return null;
    }

    /**
     * Gets a specific commit
     * @param string $revision
     * @return \ride\library\vcs\CommitLog|null
     */
    public function getCommit($revision) {
        $output = $this->client->execute($this, 'show --date=relative ' . $revision);
        $commits = $this->logParser->parseGitLog($output);

        return array_pop($commits);
    }

    /**
     * Gets the commit messages
     * @param string $path Object path
     * @param integer $number Number of commits to fetch
     * @param string $since Id of a commit
     * @param string $until Id of a commit
     * @return array Array with CommitLog instances
     * @see \ride\library\vcs\CommitLog
     */
    public function getCommits($path = null, $number = null, $since = null, $until = null) {
        $command = '--no-pager log --date=rfc --summary';

        if ($number) {
            $command .= ' -n ' . $number;
        }

        if ($since && $until) {
            $command .= ' ' . $since . '..' . $until;
        } elseif ($since) {
            $command .= ' ' . $since . '..';
        } elseif ($until) {
            $command .= ' ..' . $until;
        }

        if ($path) {
            $command .= ' -- ' . $path;
        }

        $output = $this->client->execute($this, $command);
        $commits = $this->logParser->parseGitLog($output);

        return $commits;
    }

    /**
     * Commits the current working copy to the repository
     * @param string $description Description of the commit
     * @return null
     */
    public function commit($description) {
        if ($this->hasChanges()) {
            $this->client->execute($this, 'commit -m ' . escapeshellarg($description));
        }

        $this->client->execute($this, 'push origin ' . $this->getBranch());
    }

    /**
     * Adds files to the repository
     * @param array $files
     * @return null
     */
    public function add($files = null) {
        if ($files === null) {
            $this->client->execute($this, 'add .');

            return;
        }

        $files = (array) $files;

        foreach ($files as $file) {
            $this->client->execute($this, 'add ' . $file);
        }
    }

    /**
     * Removes files from the repository
     * @param string|array $files
     * @param boolean $recursive
     * @return null
     */
    public function remove($files, $recursive = true) {
        $files = (array) $files;

        foreach ($files as $file) {
            $this->client->execute($this, 'rm -f' . ($recursive ? 'r' : '') . ' ' . $file);
        }
    }

    /**
     * Gets the tree of a branch
     * @param string $branch
     * @param string $path
     * @param boolean $recursive
     * @return array
     */
    public function getTree($branch, $path = null, $recursive = false) {
        $directories = array();
        $files = array();

        $command = 'ls-tree -l';
        if ($recursive) {
            $command .= ' -r';
        }
        $command .= ' ' . $branch;
        if ($path) {
            $command .= ' ' . $path;
        }

        $output = $this->client->execute($this, $command);
        foreach ($output as $line) {
            $object = new GitObject();

            list($attributes, $object->path) = explode("\t", $line, 2);
            list($object->mode, $object->type, $object->revision, $object->size) = explode(" ", $attributes, 4);

            $output = $this->client->execute($this, 'log --date=relative -n 1 ' . $object->path);
            $commits = $this->logParser->parseGitLog($output);

            $object->commit = array_pop($commits);

            if ($object->isDirectory()) {
                $directories[$object->path] = $object;
            } else {
                $files[$object->path] = $object;
            }
        }

        return $directories + $files;
    }

}
