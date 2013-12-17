<?php
namespace Kunstmaan\Skylab\Skeleton;

use Cilex\Application;
use Kunstmaan\Skylab\Entity\PermissionDefinition;
use Kunstmaan\Skylab\Provider\FileSystemProvider;
use Kunstmaan\Skylab\Provider\PermissionsProvider;
use Kunstmaan\Skylab\Provider\ProjectConfigProvider;
use Symfony\Component\Console\Output\OutputInterface;

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
	return BaseSkeleton::NAME;
    }


    /**
     * @param Application $app The application
     * @param \ArrayObject $project
     * @param OutputInterface $output The command output stream
     *
     * @return mixed
     */
    public function create(Application $app, \ArrayObject $project, OutputInterface $output)
    {
	/** @var $filesystem FileSystemProvider */
	$filesystem = $app["filesystem"];
	{
	    $permissionDefinition = new PermissionDefinition();
	    $permissionDefinition->setPath("/");
	    $permissionDefinition->setOwnership("@config.superuser@.@project.group@");
	    $permissionDefinition->addAcl("-R -m user::rwX");
	    $permissionDefinition->addAcl("-R -m group::r-X");
	    $permissionDefinition->addAcl("-R -m other::---");
	    $permissionDefinition->addAcl("-R -m u:" . $app["config"]["users"]["wwwuser"] . ":r-X");
	    $permissionDefinition->addAcl("-R -m u:" . $project["name"] . ":rwX");
	    $permissionDefinition->addAcl("-R -m u:" . $app["config"]["users"]["postgresuser"] . ":r-X");
	    $permissionDefinition->addAcl("-R -m group:admin:rwX");
	    $project["permissions"]["/"] = $permissionDefinition;
	}
	$filesystem->createDirectory($project, $output, '.ssh');
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
	$filesystem->createDirectory($project, $output, 'stats');
	{
	    $permissionDefinition = new PermissionDefinition();
	    $permissionDefinition->setPath("/stats");
	    $permissionDefinition->setOwnership("-R @project.user@.@project.group@");
	    $permissionDefinition->addAcl("-R -m user::rwX");
	    $permissionDefinition->addAcl("-R -m group::r-X");
	    $permissionDefinition->addAcl("-R -m u:" . $app["config"]["users"]["wwwuser"] . ":r-X");
	    $permissionDefinition->addAcl("-R -m group:admin:r-X");
	    $project["permissions"]["/stats"] = $permissionDefinition;
	}
	$filesystem->createDirectory($project, $output, 'apachelogs');
	{
	    $permissionDefinition = new PermissionDefinition();
	    $permissionDefinition->setPath("/apachelogs");
	    $permissionDefinition->setOwnership("-R @project.user@.@project.group@");
	    $permissionDefinition->addAcl("-R -m user::rwX");
	    $permissionDefinition->addAcl("-R -m group::r-X");
	    $permissionDefinition->addAcl("-R -m other::---");
	    $permissionDefinition->addAcl("-R -m u:" . $app["config"]["users"]["wwwuser"] . ":rwX");
	    $project["permissions"]["/apachelogs"] = $permissionDefinition;
	}
	$filesystem->createDirectory($project, $output, 'site');
	{
	    $permissionDefinition = new PermissionDefinition();
	    $permissionDefinition->setPath("/site");
	    $permissionDefinition->setOwnership("-R @project.user@.@project.group@");
	    $permissionDefinition->addAcl("-R -m user::rwX");
	    $permissionDefinition->addAcl("-R -m group::r-X");
	    $permissionDefinition->addAcl("-R -m other::---");
	    $permissionDefinition->addAcl("-R -m u:" . $app["config"]["users"]["wwwuser"] . ":r-X");
	    $project["permissions"]["/site"] = $permissionDefinition;
	}
	$filesystem->createDirectory($project, $output, 'backup');
	{
	    $permissionDefinition = new PermissionDefinition();
	    $permissionDefinition->setPath("/backup");
	    $permissionDefinition->setOwnership("-R @project.user@.@project.group@");
	    $permissionDefinition->addAcl("-R -m user::rwX");
	    $permissionDefinition->addAcl("-R -m group::r-X");
	    $permissionDefinition->addAcl("-R -m other::---");
	    $permissionDefinition->addAcl("-R -m u:" . $app["config"]["users"]["postgresuser"] . ":rwX");
	    $project["permissions"]["/backup"] = $permissionDefinition;
	}
	$filesystem->createDirectory($project, $output, 'data');
	{
	    $permissionDefinition = new PermissionDefinition();
	    $permissionDefinition->setPath("/data");
	    $permissionDefinition->setOwnership("-R @project.user@.@project.group@");
	    $permissionDefinition->addAcl("-R -m user::rwX");
	    $permissionDefinition->addAcl("-R -m group::r-X");
	    $permissionDefinition->addAcl("-R -m other::---");
	    $project["permissions"]["/data"] = $permissionDefinition;
	}
	$filesystem->createDirectory($project, $output, 'conf');
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
     * @param Application $app The application
     * @param \ArrayObject $project
     * @param OutputInterface $output The command output stream
     *
     * @return mixed
     */
    public function maintenance(Application $app, \ArrayObject $project, OutputInterface $output)
    {
	/** @var $permission PermissionsProvider */
	$permission = $app["permission"];
	$permission->createGroupIfNeeded($project["name"], $output);
	$permission->createUserIfNeeded($project["name"], $project["name"], $output);
	$permission->applyOwnership($project, $output);
	$permission->applyPermissions($project, $output);    }

    /**
     * @param Application $app The application
     * @param \ArrayObject $project
     * @param OutputInterface $output The command output stream
     *
     * @return mixed
     */
    public function preBackup(Application $app, \ArrayObject $project, OutputInterface $output)
    {
	// TODO: Implement preBackup() method.
    }

    /**
     * @param Application $app The application
     * @param \ArrayObject $project
     * @param OutputInterface $output The command output stream
     *
     * @return mixed
     */
    public function postBackup(Application $app, \ArrayObject $project, OutputInterface $output)
    {
	// TODO: Implement postBackup() method.
    }

    /**
     * @param Application $app The application
     * @param \ArrayObject $project
     * @param OutputInterface $output The command output stream
     *
     * @return mixed
     */
    public function preRemove(Application $app, \ArrayObject $project, OutputInterface $output)
    {
	// TODO: Implement preRemove() method.
    }

    /**
     * @param Application $app The application
     * @param \ArrayObject $project
     * @param OutputInterface $output The command output stream
     *
     * @return mixed
     */
    public function postRemove(Application $app, \ArrayObject $project, OutputInterface $output)
    {
	// TODO: Implement postRemove() method.
    }


    /**
     * @param \Cilex\Application $app
     * @param \ArrayObject $project
     * @param \SimpleXMLElement $config The configuration array
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return \SimpleXMLElement
     */
    public function writeConfig(Application $app, \ArrayObject $project, \SimpleXMLElement $config, OutputInterface $output)
    {
	/** @var ProjectConfigProvider $projectconfig */
	$projectconfig = $app['projectconfig'];
	$config = $projectconfig->addVar($config, 'project.dir', $project["dir"]);
	$config = $projectconfig->addVar($config, 'project.name', $project["name"]);
	$config = $projectconfig->addVar($config, 'project.user', $project["name"]);
	$config = $projectconfig->addVar($config, 'project.group', $project["name"]);
	$config = $projectconfig->addVar($config, 'project.admin', $app["config"]["apache"]["admin"]);
	return $config;
    }

    /**
     * @param \Cilex\Application $app
     * @param \ArrayObject $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string[]
     */
    public function dependsOn(Application $app, \ArrayObject $project, OutputInterface $output)
    {
	return array();
    }

}