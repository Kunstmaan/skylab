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
        $tempFilename = sys_get_temp_dir() . '/apache-tomcat-7.0.59-temp.tar.gz';
        $this->dialogProvider->logCommand("Downloading Tomcat to $tempFilename");
        $this->remoteProvider->curl("http://apache.cu.be/tomcat/tomcat-7/v7.0.59/bin/apache-tomcat-7.0.59.tar.gz", "application/x-gzip", $tempFilename);
        $this->fileSystemProvider->createDirectory($project, 'tomcat');
        $tomcatFolder = $this->fileSystemProvider->getProjectDirectory($project["name"]) . '/tomcat';
        $this->processProvider->executeSudoCommand("tar xvf " . $tempFilename . ' -C ' . $tomcatFolder);

        $this->fileSystemProvider->render(
            "/tomcat/apache.d/32tomcat.conf.twig",
            $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/32tomcat",
            array()
        );
        $this->processProvider->executeSudoCommand("ln -sf " . $this->fileSystemProvider->getProjectDirectory($project["name"]) . '/tomcat/* ' . $this->fileSystemProvider->getProjectDirectory($project["name"]).'/tomcat/default');
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
    	$workerlist = array();
    	$workers = array();
    	$this->fileSystemProvider->projectsLoop(function ($project) use (&$workerlist, &$workers) {
    		$webxmlfile = $project["dir"].'/tomcat/default/conf/server.xml';
    		if (file_exists($webxmlfile)) {
    			$workerlist[] = $project['name'];
    			$xml = simplexml_load_string($this->processProvider->executeSudoCommand("cat " . $webxmlfile));
    			$connector = $xml->xpath('/Server/Service/Connector/@port');
    			$port = $connector[0]->port->__toString();
    			$workers[] = 'worker.'.$project['name'].'.type=ajp13';
    			$workers[] = 'worker.'.$project['name'].'.host=localhost';
    			$workers[] = 'worker.'.$project['name'].'.port='.$port;
    			$workers[] = '';
    		}
    	});
    	array_unshift($workers, 'worker.list='.implode(',', $workerlist));
    	$this->fileSystemProvider->writeProtectedFile("/etc/apache2/workers.properties", implode("\n", $workers));
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
