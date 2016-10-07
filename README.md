# Ride: Verson Control System Library

Simple version control abstraction library of the PHP Ride framework.

## What's In This Library

### Repository

The _Repository_ interface represents a repository in any version control system.
You can use it to get information about or to manipulate the repository.

Currently only git is implemented through the _GitRepository_ class.

### CommitLog

The _CommitLog_ class is used as a data container of a single commit. 

## Code Sample

Check this code sample to see the possibilities of this library:

```php
<?php

use ride\library\system\System;
use ride\library\vcs\git\GenericGitLogParser;
use ride\library\vcs\git\GitClient;
use ride\library\vcs\git\GitRepository;
use ride\library\vcs\Respository;

function createGitRepository(System $system) {
    $gitClient = new GitClient($system);
    $gitLogParser = new GenericGitLogParser();

    $gitRepository = new GitRepository($gitClient, $gitLogParser);
    $gitRepository->setUrl('git@github.com:all-ride/ride-lib-vcs.git');
    $gitRepository->setWorkingCopy($system->getFileSystem()->getFile('/path/to/local/copy'));
    
    // optionally, set a private key
    $gitRepository->setPrivateKey('/path/to/private.key');
    
    return $gitRepository;
}

function useRepository(Repository $repository) {
    if (!$repository->isCreated()) {
        // create the working copy the first time
        $repository->create();
        
        // perform the initial checkout to retrieve everything in the local copy
        $repository->checkout();
    }
    
    // perform an update or pull
    $repository->update();
    
    // deal with branches
    $currentBranch = $repository->getBranch();
    $availableBranches = $repository->getBranches();
    
    if (!$repository->hasBranch('my-branch')) {
        $repository->createBranch('my-branch');
    }
    
    // retrieve information about commits
    $currentRevision = $repository->getRevision();
    
    $commit = $repository->getCommit($currentRevision);
    if ($commit) {
        echo $commit->message;
        echo $commit->author;
    }
    
    $commits = $repository->getCommits();
    $commits = $repository->getCommits('src/ride/library/vcs/Repository.php');
    
    $sinceCommit = 'a1b2c3';
    $untilCommit = 'z9y8x7';
    $commits = $repository->getCommits('src/ride/library/vcs/Repository.php', 5, $sinceCommit, $untilCommit);
    
    // perform commits
    $repository->add('src/ride/library/vcs/git'); // a folder
    $repository->add('src/ride/library/vcs/git/git-ssh.sh'); // a directory
    
    $repository->remove('.gitignore');
    
    $repository->commit('added git implementation');
}
```

### Implementations

For more examples, you can check the following implementations of this library:
- [ride/app-vcs](https://github.com/all-ride/ride-app-vcs)
- [ride/web-cms-vcs](https://github.com/all-ride/ride-web-cms)

## Installation

You can use [Composer](http://getcomposer.org) to install this library.

```
composer require ride/lib-vcs
```
