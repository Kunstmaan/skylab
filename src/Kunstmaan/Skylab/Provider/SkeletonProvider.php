<?php

namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Kunstmaan\Skylab\Skeleton\AbstractSkeleton;
use RuntimeException;

/**
 * SkeletonProvider
 */
class SkeletonProvider extends AbstractProvider
{

    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['skeleton'] = $this;
        $this->app = $app;
    }

    /**
     * @param \ArrayObject     $project  The project
     * @param AbstractSkeleton $skeleton The skeleton
     */
    public function applySkeleton(\ArrayObject $project, AbstractSkeleton $skeleton)
    {
        $list = new \ArrayObject();
        $list[$skeleton->getName()] = $skeleton;
        $list = $this->resolveDependencies($skeleton, $project, $list);
        /** @var AbstractSkeleton $theSkeleton */
        foreach ($list as $theSkeleton) {
            if (!isset($project["skeletons"]) || !array_key_exists($theSkeleton->getName(), $project["skeletons"])) {
                $this->dialogProvider->logTask("Running skeleton create for " . $theSkeleton->getName());
                $theSkeleton->create($project);
                $project["skeletons"][$theSkeleton->getName()] = $theSkeleton->getName();
            }
        }
    }

    /**
     * @param  AbstractSkeleton $theSkeleton
     * @param  \ArrayObject     $project
     * @param  \ArrayObject     $deps
     * @return \ArrayObject
     */
    private function resolveDependencies(AbstractSkeleton $theSkeleton, \ArrayObject $project, \ArrayObject $deps)
    {
        $skeletonDeps = $theSkeleton->dependsOn();
        foreach ($skeletonDeps as $skeletonDependencyName) {
            if (!array_key_exists($skeletonDependencyName, $deps)) {
                $aSkeleton = $this->findSkeleton($skeletonDependencyName);
                $deps[$aSkeleton->getName()] = $aSkeleton;
                $deps = $this->resolveDependencies($aSkeleton, $project, $deps);
            }
        }

        return $deps;
    }

    /**
     * @param  string                $skeletonname
     * @return bool|AbstractSkeleton
     * @throws \RuntimeException
     */
    public function findSkeleton($skeletonname)
    {
        if (isset($this->app["config"]["skeletons"][$skeletonname])) {
            $skeleton = $this->app["config"]["skeletons"][$skeletonname];
            if ($skeleton === false) {
                return false;
            } else {
                return new $skeleton($this->app);
            }
        }
        throw new RuntimeException("Skeleton " . $skeletonname . " not found!");
    }

    /**
     *
     */
    public function listSkeletons()
    {
        foreach ($this->app["config"]["skeletons"] as $name => $class) {
            $this->dialogProvider->logTask($name);
        }
    }

    /**
     * @param $callback
     * @param \ArrayObject $skeletons
     */
    public function skeletonLoop($callback, \ArrayObject $skeletons = null)
    {
        if (!$skeletons) {
            $skeletons = new \ArrayObject(array_keys($this->app["config"]["skeletons"]));
        }
        foreach ($skeletons as $skeletonName) {
            $skeleton = $this->findSkeleton($skeletonName);
            if ($skeleton) {
                $callback($skeleton);
            }
        }
    }

}
