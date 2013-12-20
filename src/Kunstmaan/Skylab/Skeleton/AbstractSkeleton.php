<?php
namespace Kunstmaan\Skylab\Skeleton;

use Cilex\Application;
use Kunstmaan\Skylab\Provider\FileSystemProvider;
use Kunstmaan\Skylab\Provider\PermissionsProvider;
use Kunstmaan\Skylab\Provider\ProcessProvider;
use Kunstmaan\Skylab\Provider\ProjectConfigProvider;
use Kunstmaan\Skylab\Provider\SkeletonProvider;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * AbstractSkeleton
 */
abstract class AbstractSkeleton
{

    /**
     * @var FileSystemProvider
     */
    protected $filesystem;

    /**
     * @var ProjectConfigProvider
     */
    protected $projectConfig;

    /**
     * @var SkeletonProvider
     */
    protected $skeleton;

    /**
     * @var ProcessProvider
     */
    protected $process;

    /**
     * @var PermissionsProvider
     */
    protected $permission;

    /**
     * @param Application $app The app
     */
    public function __construct(Application $app)
    {
        $this->filesystem = $app['filesystem'];
        $this->permission = $app['permission'];
        $this->process = $app['process'];
        $this->projectConfig = $app['projectconfig'];
        $this->skeleton = $app['skeleton'];
    }

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @param Application $app The application
     * @param \ArrayObject $project
     * @param OutputInterface $output The command output stream
     *
     * @return mixed
     */
    abstract public function create(Application $app, \ArrayObject $project, OutputInterface $output);

    /**
     * @param Application $app The application
     * @param \ArrayObject $project
     * @param OutputInterface $output The command output stream
     *
     * @return mixed
     */
    abstract public function maintenance(Application $app, \ArrayObject $project, OutputInterface $output);

    /**
     * @param Application $app The application
     * @param \ArrayObject $project
     * @param OutputInterface $output The command output stream
     *
     * @return mixed
     */
    abstract public function preBackup(Application $app, \ArrayObject $project, OutputInterface $output);

    /**
     * @param Application $app The application
     * @param \ArrayObject $project
     * @param OutputInterface $output The command output stream
     *
     * @return mixed
     */
    abstract public function postBackup(Application $app, \ArrayObject $project, OutputInterface $output);

    /**
     * @param Application $app The application
     * @param \ArrayObject $project
     * @param OutputInterface $output The command output stream
     *
     * @return mixed
     */
    abstract public function preRemove(Application $app, \ArrayObject $project, OutputInterface $output);

    /**
     * @param Application $app The application
     * @param \ArrayObject $project
     * @param OutputInterface $output The command output stream
     *
     * @return mixed
     */
    abstract public function postRemove(Application $app, \ArrayObject $project, OutputInterface $output);

    /**
     * @param  \Cilex\Application $app
     * @param  \ArrayObject $project
     * @param  \SimpleXMLElement $config The configuration array
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return \SimpleXMLElement
     */
    abstract public function writeConfig(Application $app, \ArrayObject $project, \SimpleXMLElement $config, OutputInterface $output);

    /**
     * @param  \Cilex\Application $app
     * @param  \ArrayObject $project
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return string[]
     */
    abstract public function dependsOn(Application $app, \ArrayObject $project, OutputInterface $output);

}
