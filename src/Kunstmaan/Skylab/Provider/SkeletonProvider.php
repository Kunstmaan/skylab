<?php

namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Kunstmaan\Skylab\Skeleton\AbstractSkeleton;
use Kunstmaan\Skylab\Utility\DependencySolver;
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
        $dependencies = new DependencySolver();
        $this->resolveDependencies($skeleton, $dependencies);
        foreach ($dependencies->getLoadOrder() as $theSkeletonName) {
            if (!isset($project["skeletons"]) || !array_key_exists($theSkeletonName, $project["skeletons"])) {
                $theSkeleton = $this->findSkeleton($theSkeletonName);
                $this->dialogProvider->logTask("Running skeleton create for " . $theSkeleton->getName());
                $theSkeleton->create($project);
                $project["skeletons"][$theSkeleton->getName()] = $theSkeleton->getName();
            }
        }
    }

    /**
     * @param  AbstractSkeleton                           $theSkeleton
     * @param  \Kunstmaan\Skylab\Utility\DependencySolver $dependencies
     * @return \ArrayObject
     */
    private function resolveDependencies(AbstractSkeleton $theSkeleton, DependencySolver $dependencies)
    {
        if (!$dependencies->itemExists($theSkeleton->getName())) {
            $skeletonDeps = $theSkeleton->dependsOn();
            $dependencies->add($theSkeleton->getName(), $skeletonDeps);
            foreach ($skeletonDeps as $skeletonDependencyName) {
                $aSkeleton = $this->findSkeleton($skeletonDependencyName);
                $this->resolveDependencies($aSkeleton, $dependencies);
            }
        }
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
        foreach (array_keys($this->app["config"]["skeletons"]) as $name) {
            $this->dialogProvider->logTask($name);
        }
    }

    /**
     * @param \Closure $callback
     * @param \ArrayObject $skeletons
     */
    public function skeletonLoop($callback, \ArrayObject $skeletons = null)
    {
        if (!$skeletons) {
            $skeletons = new \ArrayObject(array_keys($this->app["config"]["skeletons"]));
        }
        $dependencies = new DependencySolver();
        foreach ($skeletons as $skeleton) {
            $theSkeleton = $this->findSkeleton($skeleton);
            if ($theSkeleton) {
                $this->resolveDependencies($theSkeleton, $dependencies);
            }
        }
        foreach ($dependencies->getLoadOrder() as $skeletonName) {
            $skeleton = $this->findSkeleton($skeletonName);
            if ($skeleton) {
                $callback($skeleton);
            }
        }
    }

}
