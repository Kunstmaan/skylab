<?php
namespace Kunstmaan\Skylab\Skeleton;

use Kunstmaan\Skylab\Entity\PermissionDefinition;

/**
 * BaseSkeleton
 */
class BaseSkeleton extends AbstractSkeleton
{

    const NAME = "base";

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
        {
            $permissionDefinition = new PermissionDefinition();
            $permissionDefinition->setPath("/");
            $permissionDefinition->setOwnership("@config.superuser@.@project.group@");
            $permissionDefinition->addAcl("-R -m user::rwX");
            $permissionDefinition->addAcl("-R -m group::r-X");
            $permissionDefinition->addAcl("-R -m other::---");
            $permissionDefinition->addAcl("-R -m u:@config.wwwuser@:r-X");
            $permissionDefinition->addAcl("-R -m u:@project.user@:rwX");
            $permissionDefinition->addAcl("-R -m u:@config.postgresuser@:r-X");
            $permissionDefinition->addAcl("-R -m group:admin:rwX");
            $project["permissions"]["/"] = $permissionDefinition;
        }
        $this->fileSystemProvider->createDirectory($project, '.ssh');
        {
            $permissionDefinition = new PermissionDefinition();
            $permissionDefinition->setPath("/.ssh");
            $permissionDefinition->setOwnership("-R @project.user@.@project.group@");
            $permissionDefinition->addAcl("-R -m user::rwX");
            $permissionDefinition->addAcl("-R -m group::---");
            $permissionDefinition->addAcl("-R -m other::---");
            $permissionDefinition->addAcl("-R -m m::---");
            $project["permissions"]["/.ssh"] = $permissionDefinition;
        }
        $this->fileSystemProvider->createDirectory($project, 'stats');
        {
            $permissionDefinition = new PermissionDefinition();
            $permissionDefinition->setPath("/stats");
            $permissionDefinition->setOwnership("-R @project.user@.@project.group@");
            $permissionDefinition->addAcl("-R -m user::rwX");
            $permissionDefinition->addAcl("-R -m group::r-X");
            $permissionDefinition->addAcl("-R -m u:@config.wwwuser@:r-X");
            $permissionDefinition->addAcl("-R -m group:admin:r-X");
            $project["permissions"]["/stats"] = $permissionDefinition;
        }
        $this->fileSystemProvider->createDirectory($project, 'apachelogs');
        {
            $permissionDefinition = new PermissionDefinition();
            $permissionDefinition->setPath("/apachelogs");
            $permissionDefinition->setOwnership("-R @project.user@.@project.group@");
            $permissionDefinition->addAcl("-R -m user::rwX");
            $permissionDefinition->addAcl("-R -m group::r-X");
            $permissionDefinition->addAcl("-R -m other::---");
            $permissionDefinition->addAcl("-R -m u:@config.wwwuser@:rwX");
            $project["permissions"]["/apachelogs"] = $permissionDefinition;
        }
        $this->fileSystemProvider->createDirectory($project, 'site');
        {
            $permissionDefinition = new PermissionDefinition();
            $permissionDefinition->setPath("/site");
            $permissionDefinition->setOwnership("-R @project.user@.@project.group@");
            $permissionDefinition->addAcl("-R -m user::rwX");
            $permissionDefinition->addAcl("-R -m group::r-X");
            $permissionDefinition->addAcl("-R -m other::---");
            $permissionDefinition->addAcl("-R -m u:@config.wwwuser@:r-X");
            $project["permissions"]["/site"] = $permissionDefinition;
        }
        $this->fileSystemProvider->createDirectory($project, 'backup');
        {
            $permissionDefinition = new PermissionDefinition();
            $permissionDefinition->setPath("/backup");
            $permissionDefinition->setOwnership("-R @project.user@.@project.group@");
            $permissionDefinition->addAcl("-R -m user::rwX");
            $permissionDefinition->addAcl("-R -m group::r-X");
            $permissionDefinition->addAcl("-R -m other::---");
            $permissionDefinition->addAcl("-R -m u:@config.postgresuser@:rwX");
            $project["permissions"]["/backup"] = $permissionDefinition;
        }
        $this->fileSystemProvider->createDirectory($project, 'data');
        $this->fileSystemProvider->createDirectory($project, 'data/' . $project['name']);
        $this->fileSystemProvider->createSymlink($project, 'data/' . $project['name'], 'data/current');
        {
            $permissionDefinition = new PermissionDefinition();
            $permissionDefinition->setPath("/data");
            $permissionDefinition->setOwnership("-R @project.user@.@project.group@");
            $permissionDefinition->addAcl("-R -m user::rwX");
            $permissionDefinition->addAcl("-R -m group::r-X");
            $permissionDefinition->addAcl("-R -m other::---");
            $project["permissions"]["/data"] = $permissionDefinition;
        }
        $this->fileSystemProvider->createDirectory($project, 'conf');
        {
            $permissionDefinition = new PermissionDefinition();
            $permissionDefinition->setPath("/conf");
            $permissionDefinition->setOwnership("-R @config.superuser@.@project.group@");
            $permissionDefinition->addAcl("-R -m user::rwX");
            $permissionDefinition->addAcl("-R -m group::r-X");
            $permissionDefinition->addAcl("-R -m other::---");
            $project["permissions"]["/conf"] = $permissionDefinition;
        }

        $project["backupexcludes"]["error.log"] = "error.log";
        $project["backupexcludes"]["access.log*"] = "access.log*";
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
        $this->permissionsProvider->removeUser($project["name"], $project["name"]);
    }

    /**
     * @param  \ArrayObject      $project
     * @param  \SimpleXMLElement $config  The configuration array
     * @return \SimpleXMLElement
     */
    public function writeConfig(\ArrayObject $project, \SimpleXMLElement $config)
    {
        $config = $this->projectConfigProvider->addVar($config, 'project.dir', $project["dir"]);
        $config = $this->projectConfigProvider->addVar($config, 'project.name', $project["name"]);
        $config = $this->projectConfigProvider->addVar($config, 'project.user', $project["name"]);
        $config = $this->projectConfigProvider->addVar($config, 'project.group', $project["name"]);
        $config = $this->projectConfigProvider->addVar($config, 'project.admin', $this->app["config"]["apache"]["admin"]);

        return $config;
    }

    /**
     * @return string[]
     */
    public function dependsOn()
    {
        return array();
    }

    /**
     * @param \ArrayObject $project
     * @param bool         $log
     */
    public function setPermissions(\ArrayObject $project, $log = false)
    {
        if ($log) {
            $this->dialogProvider->logTask("Updating permissions and ownership");
        }
        $this->permissionsProvider->createGroupIfNeeded($project["name"]);
        $this->permissionsProvider->createUserIfNeeded($project["name"], $project["name"]);
        $this->permissionsProvider->applyOwnership($project);
        $this->permissionsProvider->applyPermissions($project);
    }

}
