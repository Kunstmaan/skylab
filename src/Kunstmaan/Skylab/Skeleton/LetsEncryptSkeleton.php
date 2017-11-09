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
        if (isset($this->app["config"]["env"]) && $this->app["config"]["env"] == "prod") {
            $le = $this;
            $this->fileSystemProvider->projectsLoop(function ($project) use ($le) {
                if ($le->skeletonProvider->hasSkeleton($project, $le)) {
                    /** @var SslSkeleton $sslSkeleton */
                    $sslSkeleton = $le->skeletonProvider->findSkeleton(SslSkeleton::NAME);
                    if ($le->skeletonProvider->hasSkeleton($project, $sslSkeleton) && $sslSkeleton->hasRequiredSslConfiguration($project)) {
                        $le->dialogProvider->logWarning("Skippgin letsencrypt for project " . $project["name"] . ": SSL skeleton is defined and configuration is available.");
                    } else {
                        $urls = $project["aliases"];
                        $urls[] = $project["url"];
                        $leEmail = $project["letsencrypt.email"] ? $project["letsencrypt.email"] : "it@kunstmaan.be";
                        if ($le->processProvider->commandExists("letsencrypt")) {
                            $le->dialogProvider->logTask("Running letsencrypt command for project " . $project["name"]);
                            $le->processProvider->executeSudoCommand("letsencrypt --text --rsa-key-size 4096 --email " . $leEmail ." --agree-tos --keep-until-expiring --apache --apache-le-vhost-ext .ssl.conf --redirect -d " . implode(",", $urls) );
                            //Add the renew cronjob
                            $le->processProvider->executeSudoCommand("crontab -l | grep '". implode(",", $urls) . "' || (crontab -l; echo '0 0 * * 0 letsencrypt --apache -n certonly -d " . implode(",", $urls) . "') | crontab -");
                        } else {
                            $le->dialogProvider->logWarning("The command letsencrypt is not available");
                        }
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
        $urls = $project["aliases"];
        $urls[] = $project["url"];
        $this->processProvider->executeSudoCommand("crontab -l | grep -v '" . implode(",", $urls) . "' | crontab -");
    }

    /**
     * @return string[]
     */
    public function dependsOn()
    {
        return array("apache");
    }
}
