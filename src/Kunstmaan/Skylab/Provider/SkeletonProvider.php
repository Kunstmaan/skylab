<?php

namespace Kunstmaan\Skylab\Provider;

use Kunstmaan\Skylab\Skeleton\AbstractSkeleton;
use Kunstmaan\Skylab\Utility\DependencySolver;
use Pimple\Container;
use RuntimeException;

/**
 * SkeletonProvider
 */
class SkeletonProvider extends AbstractProvider
{

    /**
     * Registers services on the given app.
     *
     * @param Container $app An Application instance
     */
    public function register(Container $app)
    {
        $app['skeleton'] = $this;
        $this->app = $app;
    }

    /**
     * @param \ArrayObject     $project  The project
     * @param AbstractSkeleton $skeleton The skeleton
     *
     * @return bool
     */
    public function hasSkeleton(\ArrayObject $project, AbstractSkeleton $skeleton)
    {
        return isset($project["skeletons"]) && array_key_exists($skeleton->getName(), $project["skeletons"]);
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
            $theSkeleton = $this->findSkeleton($theSkeletonName);
            if (!$this->hasSkeleton($project, $theSkeleton)) {
                $this->dialogProvider->logTask("Running skeleton create for ".$theSkeleton->getName());
                $theSkeleton->create($project);
                $project["skeletons"][$theSkeleton->getName()] = $theSkeleton->getName();
            }
        }
    }

    /**
     * @param  AbstractSkeleton                           $theSkeleton
     * @param  \Kunstmaan\Skylab\Utility\DependencySolver $dependencies
     *
     * @return \ArrayObject
     */
    private function resolveDependencies(AbstractSkeleton $theSkeleton, DependencySolver $dependencies)
    {
        if (!$dependencies->itemExists($theSkeleton->getName())) {
            $skeletonDeps = $theSkeleton->dependsOn();
            $dependencies->add($theSkeleton->getName(), $skeletonDeps);
            foreach ($skeletonDeps as $skeletonDependencyName) {
                $aSkeleton = $this->findSkeleton($skeletonDependencyName);
                if ($aSkeleton) {
                    $this->resolveDependencies($aSkeleton, $dependencies);
                }
            }
        }
    }

    /**
     * @param  string $skeletonname
     *
     * @return AbstractSkeleton
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
        throw new RuntimeException("Skeleton ".$skeletonname." not found!");
    }

    /**
     * @return void
     */
    public function listSkeletons()
    {
        foreach (array_keys($this->app["config"]["skeletons"]) as $name) {
            $this->dialogProvider->logTask($name);
        }
    }

    /**
     * @return array
     */
    public function getSkeletons()
    {
        foreach (array_keys($this->app["config"]["skeletons"]) as $name) {
            $skeletons[] = $name;
        }

        return $skeletons;
    }

    /**
     * @param \Closure     $callback
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
