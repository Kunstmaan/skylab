<?php
namespace Kunstmaan\Skylab\Skeleton;

use Cilex\Application;
use Kunstmaan\Skylab\Helper\OutputUtil;
use Kunstmaan\Skylab\Provider\FileSystemProvider;
use Kunstmaan\Skylab\Provider\ProcessProvider;
use Kunstmaan\Skylab\Provider\ProjectConfigProvider;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * ApacheSkeleton
 */
class ApacheSkeleton extends AbstractSkeleton
{

    const NAME = "apache";

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
        /** @var ProcessProvider $process */
        $process = $app["process"];
        /** @var \Twig_Environment $twig */
        $twig = $app['twig'];
        /** @var DialogHelper $dialog */
        $dialog = $app['console']->getHelperSet()->get('dialog');
        $process->executeSudoCommand("mkdir -p " . $app["config"]["apache"]["vhostdir"], $output);
        $process->executeSudoCommand("mkdir -p " . $filesystem->getProjectDirectory($project["name"]) . "/apachelogs", $output);
        $process->executeSudoCommand("mkdir -p " . $filesystem->getProjectConfigDirectory($project["name"]) . "/apache.d", $output);
        $process->executeSudoCommand("mkdir -p " . $filesystem->getProjectDirectory($project["name"]) . "/stats", $output);
        $process->executeSudoCommand("chmod -R 777 " . $filesystem->getProjectConfigDirectory($project["name"]) . "/apache.d/", $output);
        // render templates
        $finder = new Finder();
        $finder->files()->in($filesystem->getApacheConfigTemplateDir($project, $output))->name("*.conf.twig");
        /** @var SplFileInfo $config */
        foreach ($finder as $config) {
            OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "%", "Rendering " . $config->getFilename());
            $shared = $twig->render(file_get_contents("./templates/apache/apache.d/" . $config->getFilename()), array());
            file_put_contents($filesystem->getProjectConfigDirectory($project["name"]) . "/apache.d/" . str_replace(".conf.twig", "", $config->getFilename()), $shared);
        }
        // update config
        {
            // url
            $defaultUrl = $project["name"] . ".be";
            OutputUtil::newLine($output);
            if (getenv("TRAVIS")){
                $project["url"] = $defaultUrl;
            } else {
                $project["url"] = $dialog->ask($output, "\n   <question>Enter the base url: [" . $defaultUrl . "]</question> ", $defaultUrl);
            }
        }
        {
            // url aliases
            $aliases = array();
            if (getenv("TRAVIS")){
                $aliases[] = "www." . $project["url"];
            } else {
                $alias = null;
                while (1 == 1) {
                    OutputUtil::newLine($output);
                    $alias = $dialog->ask($output, "   <question>Add an url alias (leave empty to stop adding):</question> ");
                    if (empty($alias)) {
                        break;
                    } else {
                        $aliases[] = $alias;
                    }
                }
            }
            $project["aliases"] = $aliases;
        }
    }

    /**
     * @param Application $app The application
     * @param OutputInterface $output The command output stream
     *
     * @return mixed
     */
    public function preMaintenance(Application $app, OutputInterface $output)
    {
        /** @var ProcessProvider $process */
        $process = $app["process"];
        $process->executeSudoCommand("rm -Rf " . $app["config"]["apache"]["vhostdir"] . "/*", $output);
    }

    /**
     * @param Application $app The application
     * @param OutputInterface $output The command output stream
     *
     * @return mixed
     */
    public function postMaintenance(Application $app, OutputInterface $output)
    {
        OutputUtil::newLine($output);
        $this->writeHostFile($app, $output);
        OutputUtil::newLine($output);
        $this->writeNamevirtualhost($app, $output);
        OutputUtil::newLine($output);
        $this->writeFirsthost($app, $output);
    }

    /**
     * @param Application $app
     * @param OutputInterface $output
     */
    private function writeHostFile(Application $app, OutputInterface $output)
    {
        $hostmachine = $app["config"]["apache"]["hostmachine"];
        $hostlines = array();
        $this->filesystem->projectsLoop($output, function ($projectName, $skeletons, $project) use ($app, $output, $hostmachine, &$hostlines) {
            if (array_key_exists($this->getName(), $project["skeletons"])) {
                $hostlines[] = "127.0.0.1 " . $project["name"] . "." . $hostmachine . " www." . $project["name"] . "." . $hostmachine . "\n";
            }
        });
        OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Updating the <info>/etc/hosts</info> file");
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
        $this->filesystem->writeProtectedFile("/etc/hosts", implode("", $resultLines), $output);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return ApacheSkeleton::NAME;
    }

    /**
     * @param Application $app
     * @param OutputInterface $output
     */
    private function writeNamevirtualhost(Application $app, OutputInterface $output)
    {
        OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Writing <info>namevirtualhosts</info>");
        $namevirtualhosts = "NameVirtualHost *:80\n";
        $namevirtualhosts .= "NameVirtualHost *:443\n";
        $this->filesystem->writeProtectedFile($app["config"]["apache"]["vhostdir"] . "/namevirtualhosts", $namevirtualhosts, $output);
    }

    /**
     * @param Application $app
     * @param OutputInterface $output
     */
    private function writeFirsthost(Application $app, OutputInterface $output)
    {
        OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Writing <info>000firsthost.conf</info>");
        $firsthost = "<VirtualHost *:80>\n";
        $firsthost .= "    ServerName no_website_configured_at_this_address\n";
        $firsthost .= "    ServerAdmin support@kunstmaan.be\n";
        $firsthost .= "    DocumentRoot /opt/nowebsite/\n";
        $firsthost .= "    <Directory /opt/nowebsite >\n";
        $firsthost .= "        Options None\n";
        $firsthost .= "        Order allow,deny\n";
        $firsthost .= "        Allow from all\n";
        $firsthost .= "    </Directory>\n";
        $firsthost .= "    LogFormat \"%h %l %u %t \\\"%r\\\" %>s %b \\\"%{Referer}i\\\" \\\"%{User-Agent}i\\\" %A \\\"%{Host}i\\\" \\\"%q\\\"  \" nositelog\n";
        $firsthost .= "    ErrorLog /dev/null\n";
        $firsthost .= "    CustomLog /var/log/nowebsite.log nositelog\n";
        $firsthost .= "</VirtualHost>\n";
        $firsthost .= "<VirtualHost *:443>\n";
        $firsthost .= "    ServerName no_website_configured_at_this_address\n";
        $firsthost .= "    ServerAdmin support@kunstmaan.be\n";
        $firsthost .= "    DocumentRoot /opt/nowebsite/\n";
        $firsthost .= "    <Directory /opt/nowebsite >\n";
        $firsthost .= "        Options None\n";
        $firsthost .= "        Order allow,deny\n";
        $firsthost .= "        Allow from all\n";
        $firsthost .= "    </Directory>\n";
        $firsthost .= "    LogFormat \"%h %l %u %t \\\"%r\\\" %>s %b \\\"%{Referer}i\\\" \\\"%{User-Agent}i\\\" %A \\\"%{Host}i\\\" \\\"%q\\\"  \" nositelog\n";
        $firsthost .= "    ErrorLog /dev/null\n";
        $firsthost .= "    CustomLog /var/log/nowebsite.log nositelog\n";
        $firsthost .= "</VirtualHost>\n";
        $this->filesystem->writeProtectedFile($app["config"]["apache"]["vhostdir"] . "/000firsthost.conf", $firsthost, $output);
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
        /** @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];
        /** @var ProjectConfigProvider $projectConfig */
        $projectConfig = $app["projectconfig"];

        OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "%", "Updating <info>05aliases</info> apache config file");
        $hostmachine = $app["config"]["apache"]["hostmachine"];
        $serverAlias = "ServerAlias " . $project["name"] . "." . $hostmachine . " www." . $project["name"] . "." . $hostmachine;
        if (isset($project["aliases"])) {
            foreach ($project["aliases"] as $alias) {
                $serverAlias .= " " . $alias;
            }
        }
        $serverAlias .= "\n";
        file_put_contents($filesystem->getProjectConfigDirectory($project["name"]) . "/apache.d/05aliases", $serverAlias);

        $configcontent = "";
        /** @var \SplFileInfo $config */
        foreach ($filesystem->getProjectApacheConfigs($project) as $config) {
            $configcontent .= "\n#BEGIN " . $config->getRealPath() . "\n\n";
            $configcontent .= $projectConfig->searchReplacer(file_get_contents($config->getRealPath()), $project);
            $configcontent .= "\n#END " . $config->getRealPath() . "\n\n";
        }
        if ($app["config"]["permissions"]["develmode"]) {
            $configcontent = str_replace("-Indexes", "Indexes", $configcontent);
        }
        file_put_contents($app["config"]["apache"]["vhostdir"] . "/" . $project["name"] . ".conf", $configcontent);
    }

    /**
     * @param Application $app The application
     * @param \ArrayObject $project
     * @param OutputInterface $output The command output stream
     *
     * @return mixed
     */
    public function preBackup(Application $app, \ArrayObject $project, OutputInterface $output)
    {
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
    }

    /**
     * @param  \Cilex\Application $app
     * @param  \ArrayObject $project
     * @param  \SimpleXMLElement $config The configuration array
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return \SimpleXMLElement
     */
    public function writeConfig(Application $app, \ArrayObject $project, \SimpleXMLElement $config, OutputInterface $output)
    {
        /** @var ProjectConfigProvider $projectconfig */
        $projectconfig = $app['projectconfig'];
        $config = $projectconfig->addVar($config, 'project.url', $project["url"]);
        if (isset($project["aliases"])) {
            $config = $projectconfig->addVarWithItems($config, 'project.aliases', $project["aliases"]);
        }
        return $config;
    }

    /**
     * @param  \Cilex\Application $app
     * @param  \ArrayObject $project
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return string[]
     */
    public function dependsOn(Application $app, \ArrayObject $project, OutputInterface $output)
    {
        return array("base", "awstats", "logrotate", "pingdom");
    }

}
