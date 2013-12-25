<?php
namespace Kunstmaan\Skylab\Skeleton;

/**
 * ApacheSkeleton
 */
class JavaSkeleton extends AbstractSkeleton
{

    const NAME = "java";

    /**
     * @return string
     */
    public function getName()
    {
        return JavaSkeleton::NAME;
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function create(\ArrayObject $project)
    {
        // TODO: Implement create() method.
    }

    /**
     * @return mixed
     */
    public function preMaintenance()
    {
        // TODO: Implement preMaintenance() method.
    }

    /**
     * @return mixed
     */
    public function postMaintenance()
    {
        // TODO: Implement postMaintenance() method.
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function maintenance(\ArrayObject $project)
    {
        // TODO: Implement maintenance() method.
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function preBackup(\ArrayObject $project)
    {
        // TODO: Implement preBackup() method.
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function postBackup(\ArrayObject $project)
    {
        // TODO: Implement postBackup() method.
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function preRemove(\ArrayObject $project)
    {
        // TODO: Implement preRemove() method.
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function postRemove(\ArrayObject $project)
    {
        // TODO: Implement postRemove() method.
    }

    /**
     * @return string[]
     */
    public function dependsOn()
    {
        return array("base", "apache", "tomcat", "postgres");
    }

}
