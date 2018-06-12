<?php
namespace Kunstmaan\Skylab\Skeleton;

/**
 * ApacheSkeleton
 */
class AnacronSkeleton extends AbstractSkeleton
{

    const NAME = "anacron";

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function create(\ArrayObject $project)
    {
        $this->processProvider->executeSudoCommand("mkdir -p " . $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/fcron.d/");
        $this->processProvider->executeSudoCommand("touch " . $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/anacrontab");
    }

    /**
     * @return mixed
     */
    public function preMaintenance()
    {
    }

    /**
     * @return mixed
     */
    public function postMaintenance()
    {
    }

    /**
     * @param  \ArrayObject $project
     * @return mixed
     */
    public function maintenance(\ArrayObject $project)
    {
        $cronAllowFile = "/etc/cron.allow";
        if (file_exists($cronAllowFile)) {
            $grepOutput = $this->processProvider->executeSudoCommand("grep -wF -- " . $project["name"] . " " . $cronAllowFile, true);
            if (empty($grepOutput)) {
                $this->fileSystemProvider->writeProtectedFile($cronAllowFile, $project["name"] . "\n", true);
            }
        }

        $this->permissionsProvider->createGroupIfNeeded($project);
        $this->permissionsProvider->createUserIfNeeded($project);

        $cronjobscript = $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/anacronjobs";
        $crontab = $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/anacrontab";

        // cleanup
        $this->processProvider->executeSudoCommand("rm -f " . $cronjobscript);
        $this->processProvider->executeSudoCommand("rm -f " . $crontab);
        $this->processProvider->executeSudoCommand("crontab -r -u " . $project["name"], true);

        // generate anacronjobs file
        if (!$this->app["config"]["develmode"]) {

            // anacrontab
            $this->processProvider->executeSudoCommand('touch ' . $crontab);
            $this->processProvider->executeSudoCommand('chmod a+w ' . $crontab);
            $this->processProvider->executeSudoCommand('echo "MAILTO=cron@kunstmaan.be" >> ' . $crontab);

            // anacronjobs
            $cronjobs = $this->fileSystemProvider->getDotDFiles($this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/fcron.d/");
            $this->processProvider->executeSudoCommand("echo -n -e '\n' >> " . $cronjobscript);
            foreach ($cronjobs as $cronjob) {
                $this->processProvider->executeSudoCommand("cat " . $cronjob->getRealPath() . " >> " . $cronjobscript);
                $this->processProvider->executeSudoCommand("echo -n -e '\n' >> " . $cronjobscript);
            }

            if (sizeof($cronjobs) > 0){
                $this->processProvider->executeSudoCommand("chmod +x ".$cronjobscript);
                $this->processProvider->executeSudoCommand("set -f;echo '0 3 * * * " . $cronjobscript . "' >> " . $crontab . ';set -f');
            }

            // project anacronjobs
            $projectAnacrontab = $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/data/current/app/config/anacrontab";
            if (file_exists($projectAnacrontab)) {
                $os = strtolower(PHP_OS);
                switch ($os) {
                    case 'linux': //Linux
                        $sed = 'sed -r';
                        break;
                    case 'darwin': //OSX
                        $sed = 'sed -E';
                        break;
                    default:
                        throw new \Exception("Unsupported OS: " . $os);
                        break;
                }

                $this->processProvider->executeSudoCommand("cat " . $projectAnacrontab . " | " . $sed . " 's/\/<path to>/".str_replace('/', '\/',$this->fileSystemProvider->getProjectDirectory($project["name"])) ."\/data\/current/g' >> " . $crontab);
                $this->processProvider->executeSudoCommand('echo >> ' . $crontab);
            }
            $this->processProvider->executeSudoCommand('echo >> ' . $crontab);

            // load the anacrontab file
            $this->processProvider->executeSudoCommand("crontab -u " . $project["name"] . " " . $crontab);

        }
    }

    /**
     * @param  \ArrayObject $project
     * @return mixed
     */
    public function preBackup(\ArrayObject $project)
    {
    }

    /**
     * @param  \ArrayObject $project
     * @return mixed
     */
    public function postBackup(\ArrayObject $project)
    {
    }

    /**
     * @param  \ArrayObject $project
     * @return mixed
     */
    public function preRemove(\ArrayObject $project)
    {
        // cleanup
        $this->processProvider->executeSudoCommand("crontab -r -u " . $project["name"], true);
        $cronAllowFile = "/etc/cron.allow";
        if (file_exists($cronAllowFile)) {
            $grepOutput = $this->processProvider->executeSudoCommand("grep -wF -- " . $project["name"] . " " . $cronAllowFile, true);
            if (!empty($grepOutput)) {
                $this->processProvider->executeSudoCommand("sed -i \"\" -e '/^". $project["name"] . "$/d' " . $cronAllowFile);
            }
        }
    }

    /**
     * @param  \ArrayObject $project
     * @return mixed
     */
    public function postRemove(\ArrayObject $project)
    {
    }

    /**
     * @return string[]
     */
    public function dependsOn()
    {
        return array("base");
    }

}