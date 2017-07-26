<?php

namespace Kunstmaan\Skylab\Skeleton;


class LetsEncryptSkeleton extends AbstractSkeleton
{

    const NAME = "letsencrypt";

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
        if ($this->app["config"]["env"] == "prod") {
            $le = $this;
            $this->fileSystemProvider->projectsLoop(function ($project) use ($le) {
                if ($le->skeletonProvider->hasSkeleton($project, $le)) {
                    $urls = $project["aliases"];
                    $urls[] = $project["url"];
                    if ($le->processProvider->commandExists("letsencrypt")) {
                        $le->dialogProvider->logTask("Running letsencrypt command for project " . $project["name"]);
                        $le->processProvider->executeSudoCommand("letsencrypt --text --rsa-key-size 4096 --email it@kunstmaan.be --agree-tos --keep-until-expiring --apache --apache-le-vhost-ext .ssl.conf --redirect -d " . implode(",", $urls) );
                    } else {
                        $le->dialogProvider->logWarning("The command letsencrypt is not available");
                    }
                }
            });
        }
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function maintenance(\ArrayObject $project)
    {
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function preBackup(\ArrayObject $project)
    {
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function postBackup(\ArrayObject $project)
    {
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function preRemove(\ArrayObject $project)
    {
    }

    /**
     * @param \ArrayObject $project
     *
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
        return array("apache");
    }
}