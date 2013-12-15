<?php

namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Cilex\ServiceProviderInterface;

use Kunstmaan\Skylab\Entity\Project;
use Kunstmaan\Skylab\Helper\OutputUtil;
use Kunstmaan\Skylab\Skeleton\AbstractSkeleton;
use Kunstmaan\Skylab\Skeleton\SkeletonInterface;
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
	OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Applying " . get_class($skeleton) . " to " . $project["name"]);
	$project["skeletons"][] = $skeleton->getName();
	$this->resolveDependencies($project, $output);
	$skeleton->create($this->app, $project, $output);
    }

    /**
     * @param \ArrayObject $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function resolveDependencies(\ArrayObject $project, OutputInterface $output)
    {
	$deps = (isset($project->skeletons)?$project->skeletons:array());
	foreach ($deps as $skeletonName) {
	    $theSkeleton = $this->findSkeleton($skeletonName, $output);
	    $skeletonDeps = $theSkeleton->dependsOn($this->app, $project, $output);
	    foreach ($skeletonDeps as $skeletonDependencyName) {
		if (!isset($deps[$skeletonDependencyName])) {
		    $aSkeleton = $this->findSkeleton($skeletonDependencyName, $output);
		    $this->applySkeleton($project, $aSkeleton, $output);
		}
	    }
	}
    }

    /**
     * @param string $skeletonname
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
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
	throw new RuntimeException("Skeleton not found!");
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function listSkeletons(OutputInterface $output)
    {
	foreach ($this->app["config"]["skeletons"] as $name => $class) {
	    OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, $name);
	}
    }
}