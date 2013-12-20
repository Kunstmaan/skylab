<?php
namespace Kunstmaan\Skylab\Skeleton;

use Cilex\Application;
use Kunstmaan\Skylab\Provider\FileSystemProvider;
use Kunstmaan\Skylab\Provider\ProcessProvider;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ApacheSkeleton
 */
class AnacronSkeleton extends AbstractSkeleton
{

    const NAME = "anacron";

    /**
     * @return string
     */
    public function getName()
    {
        return AnacronSkeleton::NAME;
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
        /** @var ProcessProvider $process */
        $process = $app["process"];
        $process->executeSudoCommand("mkdir -p " . $filesystem->getProjectConfigDirectory($project["name"]) . "/fcron.d/", $output);
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
        /** @var ProcessProvider $process */
        $process = $app["process"];
        $cronjobscript = $filesystem->getProjectConfigDirectory($project["name"]) . "/anacronjobs";
        // cleanup
        $process->executeSudoCommand("rm -f " . $cronjobscript, $output);
        $process->executeSudoCommand("crontab -r -u " . $project["name"], $output, true);
        // generate anacronjobs file
        $cronjobs = $filesystem->getDotDFiles($filesystem->getProjectConfigDirectory($project["name"]) . "/fcron.d/");
        foreach ($cronjobs as $cronjob) {
            $process->executeSudoCommand("cat " . $cronjob->getRealPath() . " >> " . $cronjobscript, $output);
            $process->executeSudoCommand("sed -i -e '\$a\\' " . $cronjobscript, $output);
        }
        if (file_exists($filesystem->getProjectDirectory($project["name"]) . "data/current/app/config/anacrontab")) {
            $process->executeSudoCommand($filesystem->getProjectDirectory($project["name"]) . "data/current/app/config/anacrontab >> " . $cronjobscript, $output);
            $process->executeSudoCommand("sed -i -e '\$a\\' " . $cronjobscript, $output);
        }
        $process->executeSudoCommand('printf "\n" >> ' . $cronjobscript, $output);
        // load the anacrontab file
        $process->executeSudoCommand("crontab -u " . $project["name"] . " " . $filesystem->getProjectConfigDirectory($project["name"]) . "/anacrontab", $output);
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
        /** @var ProcessProvider $process */
        $process = $app["process"];
        // cleanup
        $process->executeSudoCommand("crontab -r -u " . $project["name"], $output, true);
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
        return array("base");
    }

}
