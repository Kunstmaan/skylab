<?php
namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Kunstmaan\Skylab\Entity\PermissionDefinition;

/**
 * PermissionsProvider
 */
class PermissionsProvider extends AbstractProvider
{

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
     */
    public function createGroupIfNeeded($groupName)
    {
        if (PHP_OS == "Darwin") {
            $this->processProvider->executeSudoCommand('dscl . create /groups/' . $groupName);
            $this->processProvider->executeSudoCommand('dscl . create /groups/' . $groupName . ' RealName ' . $groupName);
            $this->processProvider->executeSudoCommand('dscl . create /groups/' . $groupName . " name " . $groupName);
            $this->processProvider->executeSudoCommand('dscl . create /groups/' . $groupName . ' passwd "*"');
            $this->processProvider->executeSudoCommand('dscl . create /groups/' . $groupName . ' PrimaryGroupID 20');
        } else {
            if (!$this->isGroup($groupName)) {
                $this->processProvider->executeSudoCommand('addgroup ' . $groupName);
            }
        }
    }

    /**
     * @param string $groupName The group name
     *
     * @return bool|string
     */
    private function isGroup($groupName)
    {
        if (PHP_OS == "Darwin") {
            return $this->processProvider->executeSudoCommand('dscl . -list /groups | grep ^' . $groupName . '$', true);
        } else {
            return $this->processProvider->executeSudoCommand('cat /etc/group | egrep ^' . $groupName . ':', true);
        }
    }

    /**
     * @param string $userName  The user name
     * @param string $groupName The group name
     */
    public function createUserIfNeeded($userName, $groupName)
    {
        if (!$this->isUser($userName)) {
            if (PHP_OS == "Darwin") {
                $maxid = $this->processProvider->executeSudoCommand("dscl . list /Users UniqueID | awk '{print $2}' | sort -ug | tail -1");
                $maxid = $maxid + 1;
                $this->processProvider->executeSudoCommand('dscl . create /Users/' . $userName);
                $this->processProvider->executeSudoCommand('dscl . create /Users/' . $userName . ' UserShell /bin/bash');
                $this->processProvider->executeSudoCommand('dscl . create /Users/' . $userName . ' NFSHomeDirectory ' . $this->app["config"]["projects"]["path"] . "/" . $userName);
                $this->processProvider->executeSudoCommand('dscl . create /Users/' . $userName . ' PrimaryGroupID 20');
                $this->processProvider->executeSudoCommand('dscl . create /Users/' . $userName . ' UniqueID ' . $maxid);
                $this->processProvider->executeSudoCommand('dscl . append /Groups/' . $groupName . ' GroupMembership ' . $userName);
                $this->processProvider->executeSudoCommand('defaults write /Library/Preferences/com.apple.loginwindow HiddenUsersList -array-add ' . $userName);
            } else {
                $this->processProvider->executeSudoCommand('adduser --firstuid 1000 --lastuid 1999 --disabled-password --system --quiet --ingroup ' . $groupName . ' --home "'.$this->app["config"]["projects"]["path"] . "/" . $userName . '" --no-create-home --shell /bin/bash ' . $userName);
            }
        }
    }

    /**
     * @param string $userName The user name
     *
     * @return mixed
     */
    private function isUser($userName)
    {
        return $this->processProvider->executeSudoCommand('id ' . $userName, true);
    }

    /**
     * @param \ArrayObject $project The project
     */
    public function applyOwnership(\ArrayObject $project)
    {
        /** @var PermissionDefinition $pd */
        foreach ($project["permissions"] as $pd) {
            $thePath = $this->fileSystemProvider->getProjectDirectory($project["name"]) . $pd->getPath();
            if (!$pd->getOwnership()) {
                $this->dialogProvider->logNotice("No ownership information for " . $thePath . ", do not chown");
                continue;
            }
            if (!file_exists($thePath)) {
                $this->dialogProvider->logNotice($thePath . " does not exist, do not chown");
                continue;
            }
            $dirContainsNFS = $this->processProvider->executeSudoCommand("mount | grep $thePath | cat");
            if (!empty($dirContainsNFS)) {
                $this->dialogProvider->logNotice($thePath . " is on an NFS share, do not chown");
                continue;
            }
            $owner = $this->projectConfigProvider->searchReplacer($pd->getOwnership(), $project);
            if (PHP_OS == "Darwin") {
                $owner = str_replace(".", ":", $owner);
            }
            $this->processProvider->executeSudoCommand('chown -f ' . $owner . ' ' . $thePath, true);
        }
    }

    /**
     * @param \ArrayObject $project The project
     */
    public function applyPermissions(\ArrayObject $project)
    {
        if ($this->app["config"]["develmode"] || !$this->processProvider->commandExists("setfacl")) {
            if (!file_exists($this->fileSystemProvider->getProjectDirectory($project["name"]))) {
                $this->dialogProvider->logNotice($this->fileSystemProvider->getProjectDirectory($project["name"]) . " does not exist, do not chmod");

                return;
            }
            $this->processProvider->executeSudoCommand('chmod -R 777 ' . $this->fileSystemProvider->getProjectDirectory($project["name"]));
            if (!file_exists($this->fileSystemProvider->getProjectDirectory($project["name"]) . '/.ssh/')) {
                $this->dialogProvider->logNotice($this->fileSystemProvider->getProjectDirectory($project["name"]) . '/.ssh/' . " does not exist, do not chmod");

                return;
            }
            $this->processProvider->executeSudoCommand('chmod -R 700 ' . $this->fileSystemProvider->getProjectDirectory($project["name"]) . '/.ssh/');
        } else {
            /** @var PermissionDefinition $pd */
            foreach ($project["permissions"] as $pd) {
                foreach ($pd->getAcl() as $acl) {
                    $path = $this->fileSystemProvider->getProjectDirectory($project["name"]) . $pd->getPath();
                    if (!file_exists($path)) {
                        $this->dialogProvider->logNotice($path . " does not exist, do not chmod");
                        continue;
                    }
                    $result = $this->processProvider->executeSudoCommand('setfacl ' . $this->projectConfigProvider->searchReplacer($acl, $project) . ' ' . $path, true);
                }
            }
        }
        $this->processProvider->executeSudoCommand('find ' . $this->fileSystemProvider->getProjectDirectory($project["name"]) . '/ -type d -exec chmod o+rx {} \;');
    }

    /**
     * @param string $userName The user name
     */
    public function killProcesses($userName)
    {
        $this->processProvider->executeSudoCommand("su - " . $userName . " -c 'kill -9 -1'", true);
    }

    /**
     * @param string $userName  The user name
     * @param string $groupName The group name
     */
    public function removeUser($userName, $groupName)
    {
        if ($this->isUser($userName)) {
            if (PHP_OS == "Darwin") {
                $this->processProvider->executeSudoCommand('dscl . delete /Users/' . $userName);
                $this->processProvider->executeSudoCommand('dscl . delete /Groups/' . $groupName);
            } else {
                $this->processProvider->executeSudoCommand('userdel ' . $userName);
            }
        }
    }
}
