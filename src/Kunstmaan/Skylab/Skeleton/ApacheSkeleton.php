<?php
namespace Kunstmaan\Skylab\Skeleton;

use Cilex\Application;
use Kunstmaan\Skylab\Helper\OutputUtil;
use Kunstmaan\Skylab\Provider\DialogProvider;
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
     * @return string
     */
    public function getName()
    {
    return ApacheSkeleton::NAME;
    }

    /**
     * @param Application     $app     The application
     * @param \ArrayObject    $project
     * @param OutputInterface $output  The command output stream
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
        $process->executeSudoCommand("mkdir -p /etc/apache2/conf/projects.d/", $output);
        $process->executeSudoCommand("mkdir -p " . $filesystem->getProjectDirectory($project["name"]) . "/apachelogs", $output);
        $process->executeSudoCommand("mkdir -p " . $filesystem->getProjectConfigDirectory($project["name"]) . "/apache.d", $output);
        $process->executeSudoCommand("mkdir -p " . $filesystem->getProjectDirectory($project["name"]) . "/stats", $output);
        $process->executeSudoCommand("chmod -R 777 " . $filesystem->getProjectConfigDirectory($project["name"]) . "/apache.d/", $output);
        // render templates
        $finder = new Finder();
        $finder->files()->in($filesystem->getApacheConfigTemplateDir($project, $output))->name("*.conf.twig");
        /** @var SplFileInfo $config */
        foreach ($finder as $config) {
            OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "%", "Rendering " . $config->getRealPath());
            $shared = $twig->render($config->getRealPath(), array());
            file_put_contents($filesystem->getProjectConfigDirectory($project["name"]) . "/apache.d/" . str_replace(".conf.twig", "", $config->getFilename()), $shared);
        }
        // update config
        {
            // url
            $defaultUrl = $project["name"].".be";
            OutputUtil::newLine($output);
            $project["url"] = $dialog->ask($output, "\n   <question>Enter the base url: [".$defaultUrl."]</question> ", $defaultUrl);
        }
        {
            // url aliases
            $aliases = array();
            $alias = null;
            while (1==1) {
                OutputUtil::newLine($output);
                $alias = $dialog->ask($output, "   <question>Add an url alias (leave empty to stop adding):</question> ");
                if (empty($alias)) {
                    break;
                } else {
                    $aliases[] = $alias;
                }
            }
            $project["aliases"] = $aliases;
        }
    }

    /**
     * @param Application     $app     The application
     * @param \ArrayObject    $project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    public function maintenance(Application $app, \ArrayObject $project, OutputInterface $output)
    {
    // TODO: Implement maintenance() method.
    }

    /**
     * @param Application     $app     The application
     * @param \ArrayObject    $project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    public function preBackup(Application $app, \ArrayObject $project, OutputInterface $output)
    {
    // TODO: Implement preBackup() method.
    }

    /**
     * @param Application     $app     The application
     * @param \ArrayObject    $project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    public function postBackup(Application $app, \ArrayObject $project, OutputInterface $output)
    {
    // TODO: Implement postBackup() method.
    }

    /**
     * @param Application     $app     The application
     * @param \ArrayObject    $project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    public function preRemove(Application $app, \ArrayObject $project, OutputInterface $output)
    {
    // TODO: Implement preRemove() method.
    }

    /**
     * @param Application     $app     The application
     * @param \ArrayObject    $project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    public function postRemove(Application $app, \ArrayObject $project, OutputInterface $output)
    {
    // TODO: Implement postRemove() method.
    }

    /**
     * @param  \Cilex\Application                                $app
     * @param  \ArrayObject                                      $project
     * @param  \SimpleXMLElement                                 $config  The configuration array
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return \SimpleXMLElement
     */
    public function writeConfig(Application $app, \ArrayObject $project, \SimpleXMLElement $config, OutputInterface $output)
    {
        /** @var ProjectConfigProvider $projectconfig */
        $projectconfig = $app['projectconfig'];
        $config = $projectconfig->addVar($config, 'project.url', $project["url"]);
        $config = $projectconfig->addVarWithItems($config, 'project.aliases', $project["aliases"]);
        return $config;
    }

    /**
     * @param  \Cilex\Application                                $app
     * @param  \ArrayObject                                      $project
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return string[]
     */
    public function dependsOn(Application $app, \ArrayObject $project, OutputInterface $output)
    {
        return array("base");
    }

}
