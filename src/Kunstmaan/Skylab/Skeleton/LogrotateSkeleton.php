<?php
namespace Kunstmaan\Skylab\Skeleton;

use Cilex\Application;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ApacheSkeleton
 */
class LogrotateSkeleton extends AbstractSkeleton
{

    const NAME = "logrotate";

    /**
     * @return string
     */
    public function getName()
    {
	return LogrotateSkeleton::NAME;
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
	// TODO: Implement create() method.
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
	// TODO: Implement maintenance() method.
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
	// TODO: Implement preBackup() method.
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
	// TODO: Implement postBackup() method.
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
	// TODO: Implement preRemove() method.
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
	// TODO: Implement postRemove() method.
    }

    /**
     * @param \Cilex\Application $app
     * @param \ArrayObject $project
     * @param \SimpleXMLElement $config The configuration array
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return \SimpleXMLElement
     */
    public function writeConfig(Application $app, \ArrayObject $project, \SimpleXMLElement $config, OutputInterface $output)
    {
	// TODO: Implement writeConfig() method.
    }

    /**
     * @param \Cilex\Application $app
     * @param \ArrayObject $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string[]
     */
    public function dependsOn(Application $app, \ArrayObject $project, OutputInterface $output)
    {
	// TODO: Implement dependsOn() method.
    }

}