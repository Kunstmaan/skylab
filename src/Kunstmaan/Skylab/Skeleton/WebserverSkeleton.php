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

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function create(\ArrayObject $project)
    {
        $this->handleAliases($project, $aliases);
        // nginx
        $this->prepareNginxDirectories($project);
        $this->renderConfig($this->fileSystemProvider->getNginxConfigTemplateDir(),$this->fileSystemProvider->getNginxConfigTemplateDir(true),$this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/nginx.d/");
        // apache
        $this->prepareApacheDirectories($project);
        $this->renderConfig($this->fileSystemProvider->getApacheConfigTemplateDir(),$this->fileSystemProvider->getApacheConfigTemplateDir(true),$this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/");
    }

    /**
     * @return mixed
     */
    public function preMaintenance()
    {
        if ($this->app["config"]["webserver"]["engine"] == 'nginx') {
            $this->processProvider->executeSudoCommand("rm -Rf " . $this->app["config"]["nginx"]["sitesavailable"] . "/*");
            $this->processProvider->executeSudoCommand("rm -Rf " . $this->app["config"]["nginx"]["sitesenabled"] . "/*");
        } else {
            $this->processProvider->executeSudoCommand("rm -Rf " . $this->app["config"]["apache"]["vhostdir"] . "/*");
        }
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function maintenance(\ArrayObject $project)
    {
        $this->dialogProvider->logConfig("Updating aliases webserver config file");
        $this->generateBasicAliases($project, $aliases);

        if ($this->app["config"]["webserver"]["engine"] == 'nginx') {
            $this->prepareNginxDirectories($project);
            $serverName = $this->generateAliasLine($aliases, $this->app["config"]["webserver"]["engine"]);
            $this->processProvider->executeSudoCommand("rm -f " . $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/nginx.d/05servername*");
            $finder = new Finder();
            $finder->files()->in($this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/nginx.d/")->name("01-base*");
            if ($finder->count() == 0){
                $this->fileSystemProvider->writeProtectedFile($this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/nginx.d/05servername", $serverName);
            }
            $configcontent = $this->processConfigFiles($project, $this->fileSystemProvider->getProjectNginxConfigs($project));
            $this->fileSystemProvider->writeProtectedFile($this->app["config"]["nginx"]["sitesavailable"]. "/" . $project["name"] . ".conf", $configcontent);
        } else {
            $serverAlias = $this->generateAliasLine($aliases, $this->app["config"]["webserver"]["engine"]);
            $this->fileSystemProvider->writeProtectedFile($this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/05aliases", $serverAlias);
            if ($this->app["config"]["develmode"]) {
                $this->fileSystemProvider->writeProtectedFile($this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/06devmode", "SetEnv APP_ENV dev");
            } else {
                $this->processProvider->executeSudoCommand("rm -f " . $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/06devmode");
            }
            $configcontent = $this->processConfigFiles($project, $this->fileSystemProvider->getProjectApacheConfigs($project));
            $configcontent = $this->fixApache24Compat($configcontent);
            if ($this->app["config"]["develmode"]) {
                $configcontent = str_replace("-Indexes", "+Indexes", $configcontent);
            }
            $this->fileSystemProvider->writeProtectedFile($this->app["config"]["apache"]["vhostdir"] . "/" . $project["name"] . ".conf", $configcontent);
        }
    }

    private function fixApache24Compat($configcontent){
        if (strpos($configcontent, "Require all granted") === false) {
            return preg_replace('/(<Directory.*\ >)(.*)(<\/Directory>)/s', "\${1}\nOptions -Indexes +MultiViews +Includes +FollowSymLinks\nAllowOverride All\n<IfVersion < 2.4>\nOrder allow,deny\nAllow from all\n</IfVersion>\n<IfVersion >= 2.4>\nRequire all granted\n</IfVersion>\n\${3}", $configcontent);
        }
        return $configcontent;
    }

    /**
     * @return mixed
     */
    public function postMaintenance()
    {
        $this->writeHostFile();
        if ($this->app["config"]["webserver"]["engine"] == 'nginx') {
            $finder = new Finder();
            $finder->files()->in($this->app["config"]["nginx"]["sitesavailable"])->name("*.conf");
            /** @var SplFileInfo $config */
            foreach ($finder as $config) {
                $this->processProvider->executeSudoCommand("ln -sf " . $this->app["config"]["nginx"]["sitesavailable"] . "/" . $config->getFilename() . " " . $this->app["config"]["nginx"]["sitesenabled"] . "/" . $config->getFilename());
            }
        } else {
            $this->writeNamevirtualhost();
            $this->writeFirsthost();
        }
    }

    private function writeHostFile()
    {
        $hostlines = array();
        $dialogProvider = $this->dialogProvider;
        $this->fileSystemProvider->projectsLoop(function ($project) use (&$hostlines, $dialogProvider) {
            if (!array_key_exists($this->getName(), $project["skeletons"])) {
                $dialogProvider->logWarning("Project " . $project["name"] . " will not be accessible because skeleton '" . $this->getName() . "' was not applied");
                return;
            }
            $hostlines[] = $this->app["config"]["webserver"]["localip"] . " " . $project["name"] . "." . $this->app["config"]["webserver"]["hostmachine"] . " www." . $project["name"] . "." . $this->app["config"]["webserver"]["hostmachine"] . "\n";
        });
        if ($this->app["config"]["develmode"]) {
            $hostlines[] = $this->app["config"]["webserver"]["localip"] . " app.getsentry.com  #This is a workarround for disabling sentry logging in this development environment";
        }
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
        return array("base");
    }

    /**
     * @param \ArrayObject $project
     */
    private function prepareNginxDirectories(\ArrayObject $project)
    {
        $this->processProvider->executeSudoCommand("mkdir -p " . $this->app["config"]["nginx"]["sitesavailable"]);
        $this->processProvider->executeSudoCommand("mkdir -p " . $this->app["config"]["nginx"]["sitesenabled"]);
        $this->processProvider->executeSudoCommand("mkdir -p " . $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/apachelogs");
        $this->processProvider->executeSudoCommand("mkdir -p " . $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/nginx.d");
    }

    /**
     * @param \ArrayObject $project
     */
    public function prepareApacheDirectories(\ArrayObject $project)
    {
        $this->processProvider->executeSudoCommand("mkdir -p " . $this->app["config"]["apache"]["vhostdir"]);
        $this->processProvider->executeSudoCommand("mkdir -p " . $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/apachelogs");
        $this->processProvider->executeSudoCommand("mkdir -p " . $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d");
        $this->processProvider->executeSudoCommand("mkdir -p " . $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/stats");
        $this->processProvider->executeSudoCommand("chmod -R 777 " . $this->fileSystemProvider->getProjectConfigDirectory($project["name"]) . "/apache.d/");
    }

    /**
     * @param $location
     * @param $cleanedLocation
     * @param $target
     */
    private function renderConfig($location, $cleanedLocation, $target)
    {
        // render templates
        $finder = new Finder();
        $finder->files()->in($location)->name("*.conf.twig");
        foreach ($finder as $config) {
            $this->fileSystemProvider->render(
                $cleanedLocation . $config->getFilename(),
                $target . str_replace(".conf.twig", "", $config->getFilename()),
                array()
            );
        }
    }

    /**
     * @param \ArrayObject $project
     * @param $aliases
     */
    private function handleAliases(\ArrayObject &$project, &$aliases)
    {
        // url
        $defaultUrl = $project["name"] . ".be";
        $project["url"] = $this->dialogProvider->askFor("Enter the base url", null, $defaultUrl);
        // url aliases
        $this->generateBasicAliases($project, $aliases);
        $aliases = array();
        if ($this->noInteraction) {
            $this->dialogProvider->logNotice("--no-iteraction selected, using www." . $project["url"]);
            $aliases[] = "www." . $project["url"];
        } else {
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
     * @param \ArrayObject $project
     * @param $aliases
     */
    private function generateBasicAliases(\ArrayObject &$project, &$aliases)
    {
        $hostmachine = $this->app["config"]["webserver"]["hostmachine"];
        $aliases = (isset($project["aliases"])) ? $project["aliases"] : array();
        $aliases[] = $project["url"];
        $aliases[] = $project["name"] . "." . $hostmachine;
        $aliases[] = "www." . $project["name"] . "." . $hostmachine;
        if ($this->app["config"]["develmode"]) {
            $aliases[] = $project["name"] . ".*.xip.io";
            $aliases[] = "www." . $project["name"] . ".*.xip.io";
        }
    }

    /**
     * @param \ArrayObject $project
     * @return string
     */
    private function processConfigFiles(\ArrayObject $project, $configs)
    {
        $configcontent = '';
        foreach ($configs as $config) {
            $configcontent .= "\n#BEGIN " . $config->getRealPath() . "\n\n";
            $configcontent .= $this->projectConfigProvider->searchReplacer(file_get_contents($config->getRealPath()), $project) . "\n";
            $configcontent .= "\n#END " . $config->getRealPath() . "\n\n";
        }
        return $configcontent;
    }

    /**
     * @param $aliases
     * @return string
     */
    private function generateAliasLine($aliases, $type)
    {
        $serverName = ($type == 'nginx'?"server_name ":"ServerAlias ");
        foreach ($aliases as $alias) {
            $serverName .= " " . $alias;
        }
        $serverName .= ($type == 'nginx'?";\n":"\n");
        return $serverName;
    }

}
