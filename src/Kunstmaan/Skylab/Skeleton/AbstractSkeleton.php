<?php
namespace Kunstmaan\Skylab\Skeleton;

use Cilex\Application;
use Kunstmaan\Skylab\Provider\UsesProviders;

/**
 * AbstractSkeleton
 */
abstract class AbstractSkeleton
{

    use UsesProviders;

    function __construct(Application $app)
    {
        $this->setup($app);
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
