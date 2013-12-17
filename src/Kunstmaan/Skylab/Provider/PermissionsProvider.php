<?php
namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Cilex\ServiceProviderInterface;
use Kunstmaan\Skylab\Entity\Project;
use Kunstmaan\Skylab\Helper\OutputUtil;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * PermissionsProvider
 */
class PermissionsProvider implements ServiceProviderInterface
{

    /**
     * @var Application
     */
    private $app;

    /**
     * @var ProcessProvider
     */
    private $process;

    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
	$app['permission'] = $this;
	$this->app = $app;
    }

    /**
     * @param string $groupName The group name
     * @param OutputInterface $output The command output stream
     */
    public function createGroupIfNeeded($groupName, OutputInterface $output)
    {
	if (!$this->isGroup($groupName, $output)) {
	    /** @var $process ProcessProvider */
	    $process = $this->app["process"];
	    if (PHP_OS == "Darwin") {
		$process->executeCommand('dscl . create /groups/' . $groupName, $output);
		$process->executeCommand('dscl . create /groups/' . $groupName . " name " . $groupName, $output);
		$process->executeCommand('dscl . create /groups/' . $groupName . ' passwd "*"', $output);
	    } else {
		$process->executeCommand('addgroup ' . $groupName, $output);
	    }
	}
    }

    /**
     * @param string $groupName The group name
     * @param OutputInterface $output The command output stream
     *
     * @return bool|string
     */
    private function isGroup($groupName, OutputInterface $output)
    {
	/** @var ProcessProvider $process */
	$process = $this->app["process"];
	if (PHP_OS == "Darwin") {
	    return $process->executeCommand('dscl . -list /groups | grep ^' . $groupName . '$', $output, true);
	} else {
	    return $process->executeCommand('cat /etc/group | egrep ^' . $groupName . ':', $output, true);
	}
    }

    /**
     * @param string $userName The user name
     * @param string $groupName The group name
     * @param OutputInterface $output The command output stream
     */
    public function createUserIfNeeded($userName, $groupName, OutputInterface $output)
    {
	if (!$this->isUser($userName, $output)) {
	    /* @var ProcessProvider $process */
	    $process = $this->app["process"];
	    if (PHP_OS == "Darwin") {
		$maxid = $process->executeCommand("dscl . list /Users UniqueID | awk '{print $2}' | sort -ug | tail -1", $output);
		$maxid = $maxid + 1;
		$process->executeCommand('dscl . create /Users/' . $userName, $output);
		$process->executeCommand('dscl . create /Users/' . $userName . ' UserShell /bin/bash', $output);
		$process->executeCommand('dscl . create /Users/' . $userName . ' NFSHomeDirectory /var/www/' . $userName, $output);
		$process->executeCommand('dscl . create /Users/' . $userName . ' PrimaryGroupID 20', $output);
		$process->executeCommand('dscl . create /Users/' . $userName . ' UniqueID ' . $maxid, $output);
		$process->executeCommand('dscl . append /Groups/' . $groupName . ' GroupMembership ' . $userName, $output);
		$process->executeCommand('defaults write /Library/Preferences/com.apple.loginwindow HiddenUsersList -array-add ' . $userName, $output);
	    } else {
		$process->executeCommand('adduser --firstuid 1000 --lastuid 1999 --disabled-password --system --quiet --ingroup ' . $groupName . ' --home "/var/www/' . $userName . '" --no-create-home --shell /bin/bash ' . $userName, $output);
	    }
	}
    }

    /**
     * @param string $userName The user name
     * @param OutputInterface $output The command output stream
     *
     * @return mixed
     */
    private function isUser($userName, OutputInterface $output)
    {
	if (is_null($this->process)) {
	    $this->process = $this->app["process"];
	}

	return $this->process->executeCommand('id ' . $userName, $output, true);
    }

    /**
     * @param \ArrayObject $project The project
     * @param OutputInterface $output The command output stream
     */
    public function applyOwnership(\ArrayObject $project, OutputInterface $output)
    {
	if (is_null($this->process)) {
	    $this->process = $this->app["process"];
	}
	/** @var $filesystem FileSystemProvider */
	$filesystem = $this->app['filesystem'];
	/** @var ProjectConfigProvider $projectconfig */
	$projectconfig = $this->app['projectconfig'];
	foreach ($project["permissions"] as $pd) {
	    $thePath = $filesystem->getProjectDirectory($project["name"]) . $pd->getPath();
	    $dirContainsNFS = $this->process->executeCommand("mount | grep nfs | grep -q $thePath", $output);
	    if (empty($dirContainsNFS)) {
		$owner = $projectconfig->searchReplacer($pd->getOwnership(), $project);
		if (PHP_OS == "Darwin") {
		    $owner = str_replace(".", ":", $owner);
		}
		$this->process->executeSudoCommand('chown ' . $owner . ' ' . $thePath, $output);
	    } else {
		OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "!", $thePath . " is on an NFS share, do not chown");
	    }
	}
    }

    /**
     * @param \ArrayObject $project The project
     * @param OutputInterface $output The command output stream
     */
    public function applyPermissions(\ArrayObject $project, OutputInterface $output)
    {
	if (is_null($this->process)) {
	    $this->process = $this->app["process"];
	}
	/** @var $filesystem FileSystemProvider */
	$filesystem = $this->app['filesystem'];
	/** @var ProjectConfigProvider $projectconfig */
	$projectconfig = $this->app['projectconfig'];
	if ($this->app["config"]["permissions"]["develmode"]) {
	    $this->process->executeSudoCommand('chmod -R 777 ' . $filesystem->getProjectDirectory($project->getName()), $output);
	    $this->process->executeSudoCommand('chmod -R 700 ' . $filesystem->getProjectDirectory($project->getName()) . '/.ssh/', $output);
	} else {
	    foreach ($project["permissions"] as $pd) {
		foreach ($pd->getAcl() as $acl) {
		    $this->process->executeSudoCommand('setfacl ' . $projectconfig->searchReplacer($acl, $project) . ' ' . $filesystem->getProjectDirectory($project->getName()) . $pd->getPath(), $output);
		}
	    }
	}
    }

    /**
     * @param string $userName The user name
     * @param OutputInterface $output The command output stream
     */
    public function killProcesses($userName, OutputInterface $output)
    {
	if (is_null($this->process)) {
	    $this->process = $this->app["process"];
	}
	$this->process->executeCommand("su - " . $userName . " -c 'kill -9 -1'", $output, true);
    }

    /**
     * @param string $userName The user name
     * @param string $groupName The group name
     * @param OutputInterface $output The command output stream
     */
    public function removeUser($userName, $groupName, OutputInterface $output)
    {
	if ($this->isUser($userName, $output)) {
	    if (is_null($this->process)) {
		$this->process = $this->app["process"];
	    }
	    if (PHP_OS == "Darwin") {
		$this->process->executeCommand('dscl . delete /Users/' . $userName, $output);
		$this->process->executeCommand('dscl . delete /Groups/' . $groupName, $output);
	    } else {
		$this->process->executeCommand('userdel ' . $userName, $output);
	    }
	}
    }
}