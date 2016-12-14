<?php
namespace Kunstmaan\Skylab\Skeleton;

use Kunstmaan\Skylab\Entity\PermissionDefinition;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * ApacheSkeleton
 */
class PHPSkeleton extends AbstractSkeleton
{

    const NAME = "php5";

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
        if ($this->app["php-fpm_installed"]) {
            $this->fileSystemProvider->createDirectory($project, 'php-fpm');
            {
                $permissionDefinition = new PermissionDefinition();
                $permissionDefinition->setPath("/php-fpm");
                $permissionDefinition->setOwnership("-R @project.user@.@project.group@");
                $permissionDefinition->addAcl("-R -m user::rwX");
                $permissionDefinition->addAcl("-R -m group::r-X");
                $permissionDefinition->addAcl("-R -m other::---");
                $permissionDefinition->addAcl("-R -m u:@config.wwwuser@:r-X");
                $project["permissions"]["/php-fpm"] = $permissionDefinition;
            }
            $this->fileSystemProvider->createDirectory($project, 'tmp');
            {
                $permissionDefinition = new PermissionDefinition();
                $permissionDefinition->setPath("/tmp");
                $permissionDefinition->setOwnership("-R @project.user@.@project.group@");
                $permissionDefinition->addAcl("-R -m user::rwX");
                $permissionDefinition->addAcl("-R -m group::r-X");
                $permissionDefinition->addAcl("-R -m other::---");
                $permissionDefinition->addAcl("-R -m u:@config.wwwuser@:r-X");
                $project["permissions"]["/tmp"] = $permissionDefinition;
            }
        }

        $this->fileSystemProvider->render(
            "/php/nginx.d/19php.conf.twig",
            $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/nginx.d/19php",
            array()
        );
        $this->fileSystemProvider->render(
            "/php/nginx.d/10location.conf.twig",
            $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/nginx.d/10location",
            array()
        );

        $this->fileSystemProvider->renderDistConfig($this->fileSystemProvider->getConfigTemplateDir("php"),$this->fileSystemProvider->getConfigTemplateDir("php", true),$this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/");

        $this->fileSystemProvider->render(
            "/php/fcron.d/01php.twig",
            $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/fcron.d/01php",
            array(
                "projectdir" => $this->fileSystemProvider->getProjectDirectory($project["name"])
            )
        );
    }

    /**
     * @return mixed
     */
    public function preMaintenance()
    {
        if ($this->app["php-fpm_installed"]) {
            $this->processProvider->executeSudoCommand("rm -Rf /etc/php/" . $this->app['php_version'] . "/fpm/pool.d/*");
        }
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
        if ($this->app["php-fpm_installed"]) {
            $phpFpmLocation = "/etc/php/" . $this->app['php_version'] . "/fpm/pool.d/";
            $this->processProvider->executeSudoCommand("mkdir -p " . $phpFpmLocation);
            $this->fileSystemProvider->render(
                "/php/php-fpm.conf.twig",
                $phpFpmLocation . $project["name"] . ".conf",
                array(
                    "projectdir" => $this->fileSystemProvider->getProjectDirectory($project["name"]),
                    "projectname" => $project["name"],
                    "projectuser" => $project["name"],
                    "projectgroup" => $project["name"],
                    "develmode" => $this->app["config"]["develmode"]
                )
            );
        }
    }

    /**
     * @param \ArrayObject $project
     *<
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
        if ($this->app["php-fpm_installed"]) {
            $this->processProvider->executeSudoCommand("rm -f /etc/php/" . $this->app['php_version'] . "/fpm/pool.d/".$project["name"].".conf");
        }
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
        return array("base", "apache", "monitoring");
    }

}
