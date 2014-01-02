<?php
namespace Kunstmaan\Skylab\Skeleton;

/**
 * ApacheSkeleton
 */
class AwstatsSkeleton extends AbstractSkeleton
{

    const NAME = "awstats";

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
        $tempFilename = sys_get_temp_dir() . '/awstats-7.2-temp.tar.gz';

        $this->dialogProvider->logCommand("Downloading AWStats to $tempFilename");
        $this->remoteProvider->curl("http://kent.dl.sourceforge.net/project/awstats/AWStats/7.2/awstats-7.2.tar.gz", "application/x-gzip", $tempFilename);
        $statsFolder = $this->fileSystemProvider->getProjectDirectory($project["name"]) . '/stats';
        $this->processProvider->executeSudoCommand("tar xvf " . $tempFilename . ' --strip=2 --exclude="tools" --exclude="docs" -C ' . $statsFolder);
        $this->processProvider->executeSudoCommand("mkdir -p " . $statsFolder . '/data/');
        $this->processProvider->executeSudoCommand("mkdir -p " . $statsFolder . '/report/');

        $this->fileSystemProvider->render(
            "/awstats/cgi-bin/awstats.conf.twig",
            $statsFolder . "/cgi-bin/awstats.conf",
            array(
                'url' => $this->app["config"]["apache"]["admin"],
                'statsdir' => $statsFolder,
                'logdir' => $this->fileSystemProvider->getProjectDirectory($project["name"]) . '/apachelogs'
            )
        );
        $this->fileSystemProvider->render(
            "/awstats/apache.d/30awstats.conf.twig",
            $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/30awstats",
            array()
        );
        $this->fileSystemProvider->render(
            "/awstats/fcron.d/70awstats.twig",
            $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/fcron.d/70awstats",
            array()
        );
        $this->fileSystemProvider->render(
            "/awstats/report/index.php.twig",
            $statsFolder . "/report/index.php",
            array()
        );
        $this->fileSystemProvider->render(
            "/awstats/htaccess.twig",
            $statsFolder . ".htaccess",
            array(
                'statsdir' => $statsFolder,
                'projectname' => $project["name"]
            )
        );
        $this->processProvider->executeSudoCommand("htpasswd -b -c " . $statsFolder . '/.htpasswd kunstmaan ' . $this->app["config"]["awstats"]["password"]);

        // backward compatibility, no longer used
        $project["statsurl"] = "stats." . $project["name"] . ".be";
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
        $this->dialogProvider->logConfig("Updating hostaliasses config file");
        $hostmachine = $this->app["config"]["apache"]["hostmachine"];
        $serverAlias = $project["name"] . "." . $hostmachine . "\n";
        $serverAlias .= "www." . $project["name"] . "." . $hostmachine . "\n";
        if (isset($project["aliases"])) {
            foreach ($project["aliases"] as $alias) {
                $serverAlias .= $alias . "\n";
            }
        }
        $serverAlias .= "\n";
        $this->fileSystemProvider->writeProtectedFile($this->fileSystemProvider->getProjectDirectory($project["name"]) . "/stats/hostaliases", $serverAlias);
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

    public function writeConfig(\ArrayObject $project, \SimpleXMLElement $config)
    {
        $config = $this->projectConfigProvider->addVar($config, 'project.statsurl', $project["statsurl"]);

        return $config;
    }

    /**
     * @return string[]
     */
    public function dependsOn()
    {
        return array("base", "anacron", "apache");
    }

}
