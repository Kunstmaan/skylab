<?php
namespace Kunstmaan\Skylab\Skeleton;

use Kunstmaan\Skylab\Entity\PermissionDefinition;

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
        return SymfonySkeleton::NAME;
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
            $permissionDefinition->addAcl("-R -m u:" . $this->app["config"]["users"]["wwwuser"] . ":r-X");
            $project["permissions"]["/data"] = $permissionDefinition;
        }
        $this->addReadWriteFolder("/data/" . $project["name"] . "/app/cache", $project);
        $this->addReadWriteFolder("/data/" . $project["name"] . "/app/logs", $project);
        $this->addReadWriteFolder("/data/" . $project["name"] . "/web/media", $project);
        $this->addReadWriteFolder("/data/current/app/cache", $project);
        $this->addReadWriteFolder("/data/current/app/logs", $project);
        $this->addReadWriteFolder("/data/current/web/media", $project);
        $this->fileSystemProvider->render(
            "/symfony/apache.d/01start.conf.twig",
            $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/01start",
            array()
        );
        $this->fileSystemProvider->render(
            "/symfony/apache.d/10permissions.conf.twig",
            $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/10permissions",
            array()
        );
    }

    /**
     * @param $path
     * @param \ArrayObject $project
     */
    private function addReadWriteFolder($path, \ArrayObject $project)
    {
        $permissionDefinition = new PermissionDefinition();
        $permissionDefinition->setPath($path);
        $permissionDefinition->setOwnership("-R @project.user@.@project.group@");
        $permissionDefinition->addAcl("-R -m user::rwX");
        $permissionDefinition->addAcl("-R -m group::r-X");
        $permissionDefinition->addAcl("-R -m other::---");
        $permissionDefinition->addAcl("-R -m u:" . $this->app["config"]["users"]["wwwuser"] . ":rwX");
        $project["permissions"][$path] = $permissionDefinition;
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
        if ($this->app["config"]["permissions"]["develmode"]) {
            $this->processProvider->executeSudoCommand("rm " . $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/data/current");
            $this->processProvider->executeSudoCommand("ln -sf " . $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/data/" . $project["name"] . "/ " . $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/data/current");
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
        return array("base", "php5", "mysql");
    }

}
