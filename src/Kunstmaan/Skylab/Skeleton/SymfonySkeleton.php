<?php
namespace Kunstmaan\Skylab\Skeleton;

use Kunstmaan\Skylab\Entity\PermissionDefinition;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * ApacheSkeleton
 */
class SymfonySkeleton extends AbstractSkeleton
{

    const NAME = "symfony";

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
        $this->fileSystemProvider->createDirectory($project, 'data/' . $project["name"]);
        $this->fileSystemProvider->createDirectory($project, 'data');
        {
            $permissionDefinition = new PermissionDefinition();
            $permissionDefinition->setPath("/data");
            $permissionDefinition->setOwnership("-R @project.user@.@project.group@");
            $permissionDefinition->addAcl("-R -m user::rwX");
            $permissionDefinition->addAcl("-R -m group::r-X");
            $permissionDefinition->addAcl("-R -m other::---");
            $permissionDefinition->addAcl("-R -m u:@config.wwwuser@:r-X");
            $project["permissions"]["/data"] = $permissionDefinition;
        }
        $project = $this->addReadWriteFolder("/data/" . $project["name"] . "/app/cache", $project);
        $project = $this->addReadWriteFolder("/data/" . $project["name"] . "/app/logs", $project);
        $project = $this->addReadWriteFolder("/data/" . $project["name"] . "/web/media", $project);
        $project = $this->addReadWriteFolder("/data/current/app/cache", $project);
        $project = $this->addReadWriteFolder("/data/current/app/logs", $project);
        $project = $this->addReadWriteFolder("/data/current/web/media", $project);

	$this->fileSystemProvider->renderDistConfig($this->fileSystemProvider->getConfigTemplateDir("symfony"),$this->fileSystemProvider->getConfigTemplateDir("symfony",true),$this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/");

        $this->fileSystemProvider->render(
            "/symfony/nginx.d/01start.conf.twig",
            $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/nginx.d/01start",
            array()
        );
        $this->fileSystemProvider->render(
            "/symfony/nginx.d/10location.conf.twig",
            $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/nginx.d/10location",
            array()
        );
    }

    /**
     * @param $path
     * @param  \ArrayObject $project
     * @return \ArrayObject
     */
    private function addReadWriteFolder($path, \ArrayObject $project)
    {
        $permissionDefinition = new PermissionDefinition();
        $permissionDefinition->setPath($path);
        $permissionDefinition->setOwnership("-R @project.user@.@project.group@");
        $permissionDefinition->addAcl("-R -m user::rwX");
        $permissionDefinition->addAcl("-R -m group::r-X");
        $permissionDefinition->addAcl("-R -m other::---");
        $permissionDefinition->addAcl("-R -m u:@config.wwwuser@:rwX");
        $project["permissions"][$path] = $permissionDefinition;

        return $project;
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
        if ($this->app["config"]["develmode"] || !file_exists($this->fileSystemProvider->getProjectDirectory($project["name"]) . "/data/current")) {
            $this->processProvider->executeSudoCommand("rm -f " . $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/data/current");
            $this->processProvider->executeSudoCommand("ln -sf " . $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/data/" . $project["name"] . "/ " . $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/data/current");
        }
        $this->processProvider->executeSudoCommand('find '.$this->fileSystemProvider->getProjectDirectory($project["name"]).'/data/current -type d -name .git -exec cd {} "\;" -exec git config core.filemode false "\;"');
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
        return array("base", "php5", "mysql");
    }

}
