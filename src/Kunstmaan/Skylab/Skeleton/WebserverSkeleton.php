<?php
namespace Kunstmaan\Skylab\Skeleton;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * ApacheSkeleton
 */
class WebserverSkeleton extends AbstractSkeleton
{

    const NAME = "apache";


    private function prepareNginxDirectories(\ArrayObject $project){
        $this->processProvider->executeSudoCommand("mkdir -p " . $this->app["config"]["nginx"]["sitesavailable"]);
        $this->processProvider->executeSudoCommand("mkdir -p " . $this->app["config"]["nginx"]["sitesenabled"]);
        $this->processProvider->executeSudoCommand("mkdir -p " . $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/apachelogs");
        $this->processProvider->executeSudoCommand("mkdir -p " . $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/nginx.d");
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function create(\ArrayObject $project)
    {
        if ($this->app["config"]["webserver"]["engine"] == 'nginx'){
            $this->prepareNginxDirectories($project);
            $this->fileSystemProvider->render(
                "/nginx/extra.conf.twig",
                $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/nginx.d/extra.conf.twig",
                array()
            );
        } else {
            $this->processProvider->executeSudoCommand("mkdir -p " . $this->app["config"]["apache"]["vhostdir"]);
            $this->processProvider->executeSudoCommand("mkdir -p " . $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/apachelogs");
            $this->processProvider->executeSudoCommand("mkdir -p " . $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d");
            $this->processProvider->executeSudoCommand("mkdir -p " . $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/stats");
            $this->processProvider->executeSudoCommand("chmod -R 777 " . $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/");
            // render templates
            $finder = new Finder();
            $finder->files()->in($this->fileSystemProvider->getApacheConfigTemplateDir())->name("*.conf.twig");
            /** @var SplFileInfo $config */
            foreach ($finder as $config) {
                $this->fileSystemProvider->render(
                    "/apache/apache.d/" . $config->getFilename(),
                    $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/" . str_replace(".conf.twig", "", $config->getFilename()),
                    array()
                );
            }
        }
        // url
        $defaultUrl = $project["name"] . ".be";
        $project["url"] = $this->dialogProvider->askFor("Enter the base url", null, $defaultUrl);
        // url aliases
        $aliases = array();
        if ($this->noInteraction) {
            $this->dialogProvider->logNotice("--no-iteraction selected, using www." . $project["url"]);
            $aliases[] = "www." . $project["url"];
        } else {
            $alias = null;
            while (1 == 1) {
                $alias = $this->dialogProvider->askFor("Add an url alias (leave empty to stop adding):");
                if (empty($alias)) {
                    break;
                } else {
                    $aliases[] = $alias;
                }
            }
        }
        $project["aliases"] = $aliases;
    }

    /**
     * @return mixed
     */
    public function preMaintenance()
    {
        if ($this->app["config"]["webserver"]["engine"] == 'nginx'){
            $this->processProvider->executeSudoCommand("rm -Rf " . $this->app["config"]["nginx"]["sitesavailable"] . "/*");
            $this->processProvider->executeSudoCommand("rm -Rf " . $this->app["config"]["nginx"]["sitesenabled"] . "/*");
        } else {
            $this->processProvider->executeSudoCommand("rm -Rf " . $this->app["config"]["apache"]["vhostdir"] . "/*");
        }
    }

    /**
     * @return mixed
     */
    public function postMaintenance()
    {
        $this->writeHostFile();
        if ($this->app["config"]["webserver"]["engine"] == 'nginx'){
            $finder = new Finder();
            $finder->files()->in($this->app["config"]["nginx"]["sitesavailable"])->name("*.conf");
            /** @var SplFileInfo $config */
            foreach ($finder as $config) {
                $this->processProvider->executeCommand("ln -sf " . $this->app["config"]["nginx"]["sitesavailable"] . "/" . $config->getFilename() . " " . $this->app["config"]["nginx"]["sitesenabled"] . "/" . $config->getFilename());
            }
        } else {
            $this->writeNamevirtualhost();
            $this->writeFirsthost();
        }
    }

    private function writeHostFile()
    {
        $hostlines = array();
        $this->fileSystemProvider->projectsLoop(function ($project) use (&$hostlines) {
            if (array_key_exists($this->getName(), $project["skeletons"])) {
                $hostlines[] = "127.0.0.1 " . $project["name"] . "." . $this->app["config"]["webserver"]["hostmachine"] . " www." . $project["name"] . "." . $this->app["config"]["webserver"]["hostmachine"] . "\n";
            }
        });
        $this->dialogProvider->logTask("Updating the /etc/hosts file");
        $hostsfile = file("/etc/hosts");
        $resultLines = array();
        $foundSection = false;
        $inSection = false;
        foreach ($hostsfile as $line) {
            if (!$inSection) {
                if (strpos($line, "#KDEPLOY_start") === 0) {
                    $inSection = true;
                    $foundSection = true;
                    $resultLines[] = $line;
                    $resultLines = array_merge($resultLines, $hostlines);
                } else {
                    $resultLines[] = $line;
                }
            } else {
                if (strpos($line, "#KDEPLOY_end") === 0) {
                    $inSection = false;
                    $resultLines[] = $line;
                }
            }
        }
        if (!$foundSection) {
            $resultLines[] = "#KDEPLOY_start autogenerated section. do not edit below this line. do not remove this line.\n";
            $resultLines = array_merge($resultLines, $hostlines);
            $resultLines[] = "#KDEPLOY_end autogenerated section. do not edit above this line. do not remove this line.\n";
        }
        $this->fileSystemProvider->writeProtectedFile("/etc/hosts", implode("", $resultLines));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    private function writeNamevirtualhost()
    {
        $this->dialogProvider->logTask("Writing namevirtualhosts");
        $namevirtualhosts = "NameVirtualHost *:80\n";
        $namevirtualhosts .= "NameVirtualHost *:443\n";
        $this->fileSystemProvider->writeProtectedFile($this->app["config"]["apache"]["vhostdir"] . "/namevirtualhosts", $namevirtualhosts);
    }

    /**
     *
     */
    private function writeFirsthost()
    {
        $this->fileSystemProvider->render("/apache/000firsthost.conf.twig", $this->app["config"]["apache"]["vhostdir"] . "/000firsthost.conf", array(
            'admin' => $this->app["config"]["apache"]["admin"]
        ));
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function maintenance(\ArrayObject $project)
    {
        $this->dialogProvider->logConfig("Updating aliases webserver config file");
        $hostmachine = $this->app["config"]["webserver"]["hostmachine"];
        $aliases = $project["aliases"];
        $aliases[] = $project["name"] . "." . $hostmachine;
        $aliases[] = "www." .$project["name"] . "." . $hostmachine;

        if ($this->app["config"]["webserver"]["engine"] == 'nginx'){
            $this->prepareNginxDirectories($project);
            $configs = array();
            $finder = new Finder();
            $finder->files()->in($this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . '/nginx.d/')->name("*.conf");
            /** @var SplFileInfo $config */
            foreach ($finder as $config) {
                $configs[$config->getFilename()] = $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/nginx.d/" . $config->getFilename();
            }
            $this->fileSystemProvider->render(
                "/nginx/project.conf.twig",
                $this->app["config"]["nginx"]["sitesavailable"]. "/" . $project["name"] . ".conf",
                array(
                    "ports" => $project["ports"],
                    "aliases" => $aliases,
                    "root" => $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/data/current/web/",
                    "error_log" => $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/apachelogs/nginx_error.log",
                    "access_log" => $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/apachelogs/nginx_access.log",
                    "configs" => $configs
                )
            );
        } else {
            $serverAlias = "ServerAlias ";
            foreach ($aliases as $alias) {
                $serverAlias .= " " . $alias;
            }
            $serverAlias .= "\n";
            $this->fileSystemProvider->writeProtectedFile($this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/05aliases", $serverAlias);
            $configcontent = "";
            /** @var \SplFileInfo $config */
            foreach ($this->fileSystemProvider->getProjectApacheConfigs($project) as $config) {
                $configcontent .= "\n#BEGIN " . $config->getRealPath() . "\n\n";
                $configcontent .= $this->projectConfigProvider->searchReplacer(file_get_contents($config->getRealPath()), $project);
                $configcontent .= "\n#END " . $config->getRealPath() . "\n\n";
            }
            if ($this->app["config"]["develmode"]) {
                $configcontent = str_replace("-Indexes", "Indexes", $configcontent);
            }
            $this->fileSystemProvider->writeProtectedFile($this->app["config"]["apache"]["vhostdir"] . "/" . $project["name"] . ".conf", $configcontent);
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
     * @param  \ArrayObject      $project
     * @param  \SimpleXMLElement $config  The configuration array
     * @return \SimpleXMLElement
     */
    public function writeConfig(\ArrayObject $project, \SimpleXMLElement $config)
    {
        $config = $this->projectConfigProvider->addVar($config, 'project.url', $project["url"]);
        $config = $this->projectConfigProvider->addVarWithItems($config, 'project.ports', array("80", "[::]:80"));
        if (isset($project["aliases"])) {
            $config = $this->projectConfigProvider->addVarWithItems($config, 'project.aliases', $project["aliases"]);
        }

        return $config;
    }

    /**
     * @return string[]
     */
    public function dependsOn()
    {
        return array("base", "pingdom");
    }

}
