<?php
namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Cilex\ServiceProviderInterface;
use Kunstmaan\Skylab\Entity\PermissionDefinition;
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
     * @param string          $groupName The group name
     * @param OutputInterface $output    The command output stream
     */
    public function createGroupIfNeeded($groupName, OutputInterface $output)
    {
    /** @var $process ProcessProvider */
    $process = $this->app["process"];
    if (PHP_OS == "Darwin") {
        $process->executeSudoCommand('dscl . create /groups/' . $groupName, $output);
        $process->executeSudoCommand('dscl . create /groups/' . $groupName . ' RealName ' . $groupName, $output);
        $process->executeSudoCommand('dscl . create /groups/' . $groupName . " name " . $groupName, $output);
        $process->executeSudoCommand('dscl . create /groups/' . $groupName . ' passwd "*"', $output);
        $process->executeSudoCommand('dscl . create /groups/' . $groupName . ' PrimaryGroupID 20', $output);
    } else {
        if (!$this->isGroup($groupName, $output)) {
        $process->executeSudoCommand('addgroup ' . $groupName, $output);
        }
    }
    }

    /**
     * @param string          $groupName The group name
     * @param OutputInterface $output    The command output stream
     *
     * @return bool|string
     */
    private function isGroup($groupName, OutputInterface $output)
    {
    /** @var ProcessProvider $process */
    $process = $this->app["process"];
    if (PHP_OS == "Darwin") {
        return $process->executeSudoCommand('dscl . -list /groups | grep ^' . $groupName . '$', $output, true);
    } else {
        return $process->executeSudoCommand('cat /etc/group | egrep ^' . $groupName . ':', $output, true);
    }
    }

    /**
     * @param string          $userName  The user name
     * @param string          $groupName The group name
     * @param OutputInterface $output    The command output stream
     */
    public function createUserIfNeeded($userName, $groupName, OutputInterface $output)
    {
    if (!$this->isUser($userName, $output)) {
        /* @var ProcessProvider $process */
        $process = $this->app["process"];
        if (PHP_OS == "Darwin") {
        $maxid = $process->executeSudoCommand("dscl . list /Users UniqueID | awk '{print $2}' | sort -ug | tail -1", $output);
        $maxid = $maxid + 1;
        $process->executeSudoCommand('dscl . create /Users/' . $userName, $output);
        $process->executeSudoCommand('dscl . create /Users/' . $userName . ' UserShell /bin/bash', $output);
        $process->executeSudoCommand('dscl . create /Users/' . $userName . ' NFSHomeDirectory /var/www/' . $userName, $output);
        $process->executeSudoCommand('dscl . create /Users/' . $userName . ' PrimaryGroupID 20', $output);
        $process->executeSudoCommand('dscl . create /Users/' . $userName . ' UniqueID ' . $maxid, $output);
        $process->executeSudoCommand('dscl . append /Groups/' . $groupName . ' GroupMembership ' . $userName, $output);
        $process->executeSudoCommand('defaults write /Library/Preferences/com.apple.loginwindow HiddenUsersList -array-add ' . $userName, $output);
        } else {
        $process->executeSudoCommand('adduser --firstuid 1000 --lastuid 1999 --disabled-password --system --quiet --ingroup ' . $groupName . ' --home "/var/www/' . $userName . '" --no-create-home --shell /bin/bash ' . $userName, $output);
        }
    }
    }

    /**
     * @param string          $userName The user name
     * @param OutputInterface $output   The command output stream
     *
     * @return mixed
     */
    private function isUser($userName, OutputInterface $output)
    {
    if (is_null($this->process)) {
        $this->process = $this->app["process"];
    }

    return $this->process->executeSudoCommand('id ' . $userName, $output, true);
    }

    /**
     * @param \ArrayObject    $project The project
     * @param OutputInterface $output  The command output stream
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
    /** @var PermissionDefinition $pd */
    foreach ($project["permissions"] as $pd) {
        $thePath = $filesystem->getProjectDirectory($project["name"]) . $pd->getPath();
        if (!$pd->getOwnership()) {
        OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "!", "No ownership information for " . $thePath . ", do not chown");
        continue;
        }
        if (!file_exists($thePath)) {
        OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "!", $thePath . " does not exist, do not chown");
        continue;
        }
        $dirContainsNFS = $this->process->executeSudoCommand("mount | grep $thePath | cat", $output);
        if (!empty($dirContainsNFS)) {
        OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "!", $thePath . " is on an NFS share, do not chown");
        continue;
        }
        $owner = $projectconfig->searchReplacer($pd->getOwnership(), $project);
        if (PHP_OS == "Darwin") {
        $owner = str_replace(".", ":", $owner);
        }
        $this->process->executeSudoCommand('chown -f ' . $owner . ' ' . $thePath, $output, true);
    }
    }

    /**
     * @param \ArrayObject    $project The project
     * @param OutputInterface $output  The command output stream
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
    if ($this->app["config"]["permissions"]["develmode"] || !$this->process->commandExists("setfacl")) {
        if (!file_exists($filesystem->getProjectDirectory($project["name"]))) {
        OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "!", $filesystem->getProjectDirectory($project["name"]) . " does not exist, do not chmod");

        return;
        }
        $this->process->executeSudoCommand('chmod -R 777 ' . $filesystem->getProjectDirectory($project["name"]), $output);
        if (!file_exists($filesystem->getProjectDirectory($project["name"]) . '/.ssh/')) {
        OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "!", $filesystem->getProjectDirectory($project["name"]) . '/.ssh/' . " does not exist, do not chmod");

        return;
        }
        $this->process->executeSudoCommand('chmod -R 700 ' . $filesystem->getProjectDirectory($project["name"]) . '/.ssh/', $output);
    } else {
        /** @var PermissionDefinition $pd */
        foreach ($project["permissions"] as $pd) {
        foreach ($pd->getAcl() as $acl) {
            $path = $filesystem->getProjectDirectory($project["name"]) . $pd->getPath();
            if (!file_exists($path)) {
            OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "!", $path . " does not exist, do not chmod");
            continue;
            }
            $this->process->executeSudoCommand('setfacl ' . $projectconfig->searchReplacer($acl, $project) . ' ' . $path, $output);
        }
        }
    }
    $this->process->executeSudoCommand('find '.$filesystem->getProjectDirectory($project["name"]).'/ -type d -exec chmod o+rx {} \;', $output);
    }

    /**
     * @param string          $userName The user name
     * @param OutputInterface $output   The command output stream
     */
    public function killProcesses($userName, OutputInterface $output)
    {
    if (is_null($this->process)) {
        $this->process = $this->app["process"];
    }
    $this->process->executeSudoCommand("su - " . $userName . " -c 'kill -9 -1'", $output, true);
    }

    /**
     * @param string          $userName  The user name
     * @param string          $groupName The group name
     * @param OutputInterface $output    The command output stream
     */
    public function removeUser($userName, $groupName, OutputInterface $output)
    {
    if ($this->isUser($userName, $output)) {
        if (is_null($this->process)) {
        $this->process = $this->app["process"];
        }
        if (PHP_OS == "Darwin") {
        $this->process->executeSudoCommand('dscl . delete /Users/' . $userName, $output);
        $this->process->executeSudoCommand('dscl . delete /Groups/' . $groupName, $output);
        } else {
        $this->process->executeSudoCommand('userdel ' . $userName, $output);
        }
    }
    }
}
