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
        $this->processProvider->executeSudoCommand("ln -sf " . $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/05aliases " . $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/65aliases");
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


}