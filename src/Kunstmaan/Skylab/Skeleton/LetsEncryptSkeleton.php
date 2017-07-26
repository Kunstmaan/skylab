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
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function maintenance(\ArrayObject $project)
    {
        if ($this->app["config"]["env"] == "prod") {
            $urls = $project["aliases"];
            $urls[] = $project["url"];
            if ($this->processProvider->commandExists("letsencrypt")) {
                $this->processProvider->executeSudoCommand("letsencrypt --text --rsa-key-size 4096 --email it@kunstmaan.be --agree-tos --keep-until-expiring --apache --apache-le-vhost-ext .ssl.conf --redirect -d " . implode(",", $urls) );
            } else {
                $this->dialogProvider->logWarning("The command letsencrypt is not available");
            }
        }
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