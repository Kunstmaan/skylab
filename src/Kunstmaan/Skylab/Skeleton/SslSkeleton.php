<?php
namespace Kunstmaan\Skylab\Skeleton;

class SslSkeleton extends AbstractSkeleton
{

    const NAME = "ssl";

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
        $this->fileSystemProvider->createDirectory($project, 'conf/ssl/selfsigned');
        $this->processProvider->executeSudoCommand('openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout '.$this->fileSystemProvider->getProjectConfigDirectory($project["name"]).'/ssl/selfsigned/selfsigned.key -out '.$this->fileSystemProvider->getProjectConfigDirectory($project["name"]).'/ssl/selfsigned/selfsigned.crt -subj "/C=BE/ST=Vlaams-Brabant/L=Leuven/O=Kunstmaan/OU=Smarties/CN='.$project["url"].'"');

        $this->fileSystemProvider->renderDistConfig(
            $this->fileSystemProvider->getConfigTemplateDir("ssl"),
            $this->fileSystemProvider->getConfigTemplateDir("ssl",true),
            $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/"
        );
        $this->fileSystemProvider->renderConfig(
            $this->fileSystemProvider->getCustomConfigTemplateDir("ssl"),
            $this->fileSystemProvider->getCustomConfigTemplateDir("ssl",true),
            $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/"
        );
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
        if (isset($project["sslConfig"])) {
            $sslConfig = $project["sslConfig"];
            if (!$this->hasRequiredSslConfiguration($project)) {
                if (!($this->app["config"]["env"] == "prod" && $this->skeletonProvider->hasSkeleton($project, $this->skeletonProvider->findSkeleton(LetsEncryptSkeleton::NAME)))) {
                    $this->dialogProvider->logError("Required SSL configuration is missing");
                }

                return;
            }

            $this->processProvider->executeSudoCommand('mkdir -p ' . $sslConfig['webserverCertsDir']);

            $sslFiles = array("certFile" => "webserverCertFile", "certKeyFile" => "webserverCertKeyFile", "caCertFile" => "webserverCaCertFile");
            foreach ($sslFiles as $sslFile => $webserverSslFile) {
                $this->processProvider->executeSudoCommand('cp ' . $sslConfig['certsDir'] . $sslConfig[$sslFile] . ' ' . $sslConfig['webserverCertsDir'] . $sslConfig[$webserverSslFile]);
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
        return array("base", "apache");
    }

    /**
     * @param $project
     *
     * @return bool
     */
    public function hasRequiredSslConfiguration($project)
    {
        if (array_key_exists("sslConfig", $project)) {
            $sslConfig = $project["sslConfig"];

            return isset($sslConfig["certsDir"]) && isset($sslConfig["certFile"]) && isset($sslConfig["certKeyFile"]) && isset($sslConfig["caCertFile"]);
        }

        return false;
    }


}