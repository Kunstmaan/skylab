<?php
namespace Kunstmaan\Skylab\Skeleton;

use Kunstmaan\Skylab\Entity\PermissionDefinition;

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
        $this->fileSystemProvider->createDirectory($project, 'php5-fpm');
        {
            $permissionDefinition = new PermissionDefinition();
            $permissionDefinition->setPath("/php5-fpm");
            $permissionDefinition->setOwnership("-R @project.user@.@project.group@");
            $permissionDefinition->addAcl("-R -m user::rwX");
            $permissionDefinition->addAcl("-R -m group::r-X");
            $permissionDefinition->addAcl("-R -m other::---");
            $permissionDefinition->addAcl("-R -m u:@config.wwwuser@:r-X");
            $project["permissions"]["/php5-fpm"] = $permissionDefinition;
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
        $this->fileSystemProvider->render(
            "/php/apache.d/19php.conf.twig",
            $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/19php",
            array()
        );
        $this->fileSystemProvider->render(
            "/php/fcron.d/01php5.twig",
            $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/fcron.d/01php5",
            array(
                "projectdir" => $this->fileSystemProvider->getProjectDirectory($project["name"])
            )
        );
        if ($this->app["config"]["webserver"]["engine"] == 'nginx'){
            $this->writeNginxFpmConfig($project);
        }
    }

    /**
     * @return mixed
     */
    public function preMaintenance()
    {
        $this->processProvider->executeSudoCommand("rm -Rf /etc/php5/fpm/pool.d/*");
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
       if(!file_exists($this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/php5-fpm.conf")){
            $this->fileSystemProvider->render(
                "/php/php5-fpm.conf.twig",
                $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/php5-fpm.conf",
                array(
                    "projectdir" => $this->fileSystemProvider->getProjectDirectory($project["name"]),
                    "projectname" => $project["name"],
                    "projectuser" => $project["name"],
                    "projectgroup" => $project["name"],
                    "develmode" => $this->app["config"]["develmode"],
                    "slowlog_timeout" => $this->app["config"]["php"]["slowlog_timeout"]
                )
            );
        }

        $this->processProvider->executeSudoCommand("mkdir -p /etc/php5/fpm/pool.d/");
        $this->processProvider->executeSudoCommand("ln -sf " . $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/php5-fpm.conf /etc/php5/fpm/pool.d/" . $project["name"] . ".conf");

        if ($this->app["config"]["webserver"]["engine"] == 'nginx'){
            $this->writeNginxFpmConfig($project);
        }
    }


    private function writeNginxFpmConfig(\ArrayObject $project){
             $this->fileSystemProvider->render(
                "/php/nginx.d/fpm.conf.twig",
                $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/nginx.d/fpm.conf",
                array(
                    "projectdir" => $this->fileSystemProvider->getProjectDirectory($project["name"]),
                    "projectname" => $project["name"],
                )
            );
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
