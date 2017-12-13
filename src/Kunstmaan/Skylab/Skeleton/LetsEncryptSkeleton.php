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
        if (isset($this->app["config"]["env"]) && ($this->app["config"]["env"] == "prod" || $this->app["config"]["env"] == "staging")) {
            $le = $this;
            $this->fileSystemProvider->projectsLoop(function ($project) use ($le) {
                if ($le->skeletonProvider->hasSkeleton($project, $le)) {
                    /** @var SslSkeleton $sslSkeleton */
                    $sslSkeleton = $le->skeletonProvider->findSkeleton(SslSkeleton::NAME);
                    if ($le->skeletonProvider->hasSkeleton($project, $sslSkeleton) && $sslSkeleton->hasRequiredSslConfiguration($project)) {
                        $le->dialogProvider->logWarning("Skipping letsencrypt for project " . $project["name"] . ": SSL skeleton is defined and configuration is available.");
                    } else {
                        $domains = $le->getDomains($project);
                        $leEmail = array_key_exists("letsencrypt.email", $project) ? $project["letsencrypt.email"] : "it@kunstmaan.be";
                        if ($le->processProvider->commandExists("letsencrypt")) {
                            $le->dialogProvider->logTask("Running letsencrypt command for project " . $project["name"]);
                            if ($le->app["config"]["env"] == "prod") {
                                $leInstallerAndAuthenticatorMethods = "--apache";
                            } elseif ($le->app["config"]["env"] == "staging") {
                                $leInstallerAndAuthenticatorMethods = "-a webroot -i apache -w /home/projects/" . $project["name"] . "/data/current/web";
                            } else {
                                $le->dialogProvider->logWarning("Unknown environment (". $le->app["config"]["env"] . ") for letsencrypt ");

                                return;
                            }
                            //Execute the letsencrypt command
                            $le->processProvider->executeSudoCommand("letsencrypt --text --rsa-key-size 4096 --email " . $leEmail ." --agree-tos --keep-until-expiring " . $leInstallerAndAuthenticatorMethods . " --expand --no-redirect -d " . implode(",", $domains));
                            //Add the renew cronjob
                            $le->processProvider->executeSudoCommand("crontab -l | grep '". implode(",", $domains) . "' || (crontab -l; echo '0 0 * * 0 letsencrypt " . $leInstallerAndAuthenticatorMethods . " -n certonly -d " . implode(",", $domains) . "') | crontab -");
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

    /**
     * @param \ArrayObject $project
     *
     * @return array
     */
    private function getDomains(\ArrayObject $project)
    {
        $urls = [];

        if ($this->app["config"]["env"] == "prod") {
            $urls[] = $project["aliases"];
            $urls[] = $project["url"];
        } elseif ($this->app["config"]["env"] == "staging") {
            if (array_key_exists("staging_aliases", $project)) {
                $urls = $project["staging_aliases"];
            }
            $urls[] = $project["name"] . "." . $this->app["config"]["webserver"]["hostmachine"];
        }

        return $urls;
    }
}
