<?php

namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Cilex\ServiceProviderInterface;
use Kunstmaan\Skylab\Helper\OutputUtil;
use Kunstmaan\Skylab\Skeleton\AbstractSkeleton;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SkeletonProvider
 */
class SkeletonProvider implements ServiceProviderInterface
{

    /**
     * @var Application
     */
    private $app;

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
     * @param \ArrayObject $project The project
     * @param AbstractSkeleton $skeleton The skeleton
     * @param OutputInterface $output The command output stream
     */
    public function applySkeleton(\ArrayObject $project, AbstractSkeleton $skeleton, OutputInterface $output)
    {
        $list = new \ArrayObject();
        $list[$skeleton->getName()] = $skeleton;
        $list = $this->resolveDependencies($skeleton, $project, $list, $output);
        /** @var AbstractSkeleton $skeleton */
        foreach ($list as $theSkeleton) {
            if (!isset($project["skeletons"]) || !array_key_exists($theSkeleton->getName(), $project["skeletons"])) {
                OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Running skeleton create for <info>" . $theSkeleton->getName() . "</info>");
                $theSkeleton->create($this->app, $project, $output);
                $project["skeletons"][$theSkeleton->getName()] = $theSkeleton->getName();
                OutputUtil::newLine($output);
            }
        }
    }

    /**
     * @param AbstractSkeleton $theSkeleton
     * @param \ArrayObject $project
     * @param \ArrayObject $deps
     * @param OutputInterface $output
     * @return \ArrayObject
     */
    private function resolveDependencies(AbstractSkeleton $theSkeleton, \ArrayObject $project, \ArrayObject $deps, OutputInterface $output)
    {
        $skeletonDeps = $theSkeleton->dependsOn($this->app, $project, $output);
        foreach ($skeletonDeps as $skeletonDependencyName) {
            if (!array_key_exists($skeletonDependencyName, $deps)) {
                $aSkeleton = $this->findSkeleton($skeletonDependencyName, $output);
                $deps[$aSkeleton->getName()] = $aSkeleton;
                $deps = $this->resolveDependencies($aSkeleton, $project, $deps, $output);
            }
        }
        return $deps;
    }

    /**
     * @param string $skeletonname
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \RuntimeException
     * @return AbstractSkeleton
     *
     */
    public function findSkeleton($skeletonname, OutputInterface $output)
    {
        if (isset($this->app["config"]["skeletons"][$skeletonname])) {
            $skeleton = $this->app["config"]["skeletons"][$skeletonname];
            if ($skeleton === false) {
                return false;
            } else {
                return new $skeleton($this->app, $output);
            }
        }
        throw new RuntimeException("Skeleton " . $skeletonname . " not found!");
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function listSkeletons(OutputInterface $output)
    {
        foreach ($this->app["config"]["skeletons"] as $name => $class) {
            OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, $name);
            OutputUtil::newLine($output);
        }
    }

    /**
     * @param string $skeletonCommand
     * @param \ArrayObject $skeletons
     * @param string $message
     * @param Application $app
     * @param OutputInterface $output
     */
    public function skeletonLoop($skeletonCommand, \ArrayObject $skeletons, $message, Application $app, OutputInterface $output, \ArrayObject $project = null)
    {
        OutputUtil::logStep($output, OutputInterface::VERBOSITY_NORMAL, $message);
        foreach ($skeletons as $skeletonName) {
            OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Skeleton: <info>$skeletonName</info>");
            $skeleton = $this->findSkeleton($skeletonName, $output);
            if ($skeleton) {
                if ($project) {
                    $skeleton->$skeletonCommand($app, $project, $output);
                } else {
                    $skeleton->$skeletonCommand($app, $output);
                }
            }
            OutputUtil::newLine($output);
        }
    }
}
