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
        return AnacronSkeleton::NAME;
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
        $cronjobscript = $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/anacronjobs";
        $crontab = $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/anacrontab";
        // cleanup
        $this->processProvider->executeSudoCommand("rm -f " . $cronjobscript);
        $this->processProvider->executeSudoCommand("crontab -r -u " . $project["name"], true);
        // generate anacronjobs file
        $cronjobs = $this->fileSystemProvider->getDotDFiles($this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/fcron.d/");
        foreach ($cronjobs as $cronjob) {
            $this->processProvider->executeSudoCommand("cat " . $cronjob->getRealPath() . " >> " . $cronjobscript);
            $this->processProvider->executeSudoCommand("sed -i -e '\$a\\' " . $cronjobscript);
        }
        $projectAnacrontab = $this->fileSystemProvider->getProjectDirectory($project["name"]) . "data/current/app/config/anacrontab";
        if (file_exists($projectAnacrontab)) {
            $this->processProvider->executeSudoCommand($projectAnacrontab . " >> " . $cronjobscript);
            $this->processProvider->executeSudoCommand("sed -i -e '\$a\\' " . $cronjobscript);
        }
        $this->processProvider->executeSudoCommand('printf "\n" >> ' . $cronjobscript);
        // load the anacrontab file
        $this->processProvider->executeSudoCommand("crontab -u " . $project["name"] . " " . $crontab);
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
