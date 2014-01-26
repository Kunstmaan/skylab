<?php
namespace Kunstmaan\Skylab\Skeleton;

/**
 * ApacheSkeleton
 */
class TomcatSkeleton extends AbstractSkeleton
{

    const NAME = "tomcat";

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
        $tempFilename = sys_get_temp_dir() . '/apache-tomcat-7.0.50-temp.tar.gz';
        $this->dialogProvider->logCommand("Downloading Tomcat to $tempFilename");
        $this->remoteProvider->curl("http://apache.cu.be/tomcat/tomcat-7/v7.0.50/bin/apache-tomcat-7.0.50.tar.gz", "application/x-gzip", $tempFilename);
        $tomcatFolder = $this->fileSystemProvider->getProjectDirectory($project["name"]) . '/tomcat';
        $this->processProvider->executeSudoCommand("tar xvf " . $tempFilename . ' -C ' . $tomcatFolder);

        $this->fileSystemProvider->render(
            "/tomcat/apache.d/32tomcat.conf.twig",
            $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/32tomcat",
            array()
        );
    }

    /**
     * @return mixed
     */
    public function preMaintenance()
    {
        // TODO: Implement preMaintenance() method.
    }

    /**
     * @return mixed
     */
    public function postMaintenance()
    {
        // TODO: Implement postMaintenance() method.
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function maintenance(\ArrayObject $project)
    {
        // TODO: Implement maintenance() method.
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function preBackup(\ArrayObject $project)
    {
        // TODO: Implement preBackup() method.
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function postBackup(\ArrayObject $project)
    {
        // TODO: Implement postBackup() method.
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function preRemove(\ArrayObject $project)
    {
        // TODO: Implement preRemove() method.
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function postRemove(\ArrayObject $project)
    {
        // TODO: Implement postRemove() method.
    }

    /**
     * @return string[]
     */
    public function dependsOn()
    {
        return array("base", "apache");
    }

}
