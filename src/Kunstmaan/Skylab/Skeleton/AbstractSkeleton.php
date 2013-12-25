<?php
namespace Kunstmaan\Skylab\Skeleton;

use Cilex\Application;
use Kunstmaan\Skylab\Provider\DialogProvider;
use Kunstmaan\Skylab\Provider\FileSystemProvider;
use Kunstmaan\Skylab\Provider\PermissionsProvider;
use Kunstmaan\Skylab\Provider\ProcessProvider;
use Kunstmaan\Skylab\Provider\ProjectConfigProvider;
use Kunstmaan\Skylab\Provider\SkeletonProvider;

/**
 * AbstractSkeleton
 */
abstract class AbstractSkeleton
{

    /**
     * @var FileSystemProvider
     */
    protected $fileSystemProvider;

    /**
     * @var ProjectConfigProvider
     */
    protected $projectConfigProvider;

    /**
     * @var SkeletonProvider
     */
    protected $skeletonProvider;

    /**
     * @var ProcessProvider
     */
    protected $processProvider;

    /**
     * @var PermissionsProvider
     */
    protected $permissionsProvider;

    /**
     * @var DialogProvider
     */
    protected $dialogProvider;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $providers = array(
            'filesystem' => 'fileSystemProvider',
            'projectconfig' => 'projectConfigProvider',
            'skeleton' => 'skeletonProvider',
            'process' => 'processProvider',
            'permission' => 'permissionsProvider',
            'dialog' => 'dialogProvider',
            'twig' => 'twig'
        );
        foreach ($providers as $service => $variable) {
            $this->$variable = $app[$service];
        }

        $this->app = $app;
        $this->twig = $app['twig'];
    }

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    abstract public function create(\ArrayObject $project);

    /**
     * @return mixed
     */
    abstract public function preMaintenance();

    /**
     * @return mixed
     */
    abstract public function postMaintenance();

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    abstract public function maintenance(\ArrayObject $project);

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    abstract public function preBackup(\ArrayObject $project);

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    abstract public function postBackup(\ArrayObject $project);

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    abstract public function preRemove(\ArrayObject $project);

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    abstract public function postRemove(\ArrayObject $project);

    /**
     * @param  \ArrayObject $project
     * @param  \SimpleXMLElement $config The configuration array
     * @return \SimpleXMLElement
     */
    public function writeConfig(/** @noinspection PhpUnusedParameterInspection */
        \ArrayObject $project, \SimpleXMLElement $config)
    {
        return $config;
    }

    /**
     * @return string[]
     */
    abstract public function dependsOn();

}
