<?php

namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Cilex\ServiceProviderInterface;
use Kunstmaan\Skylab\Entity\Project;
use Kunstmaan\Skylab\Helper\OutputUtil;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Dumper;

/**
 * ProjectConfigProvider
 */
class ProjectConfigProvider implements ServiceProviderInterface
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
	$app['projectconfig'] = $this;
	$this->app = $app;
    }

    /**
     * @param string $projectname The project name
     * @param OutputInterface $output The command output stream
     *
     * @return Project
     */
    public function createNewProjectConfig($projectname, OutputInterface $output)
    {
	/** @var $filesystem FileSystemProvider */
	$filesystem = $this->app['filesystem'];
	$configPath = $filesystem->getProjectConfigDirectory($projectname) . "/project.yml";
	OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Creating new Project object named $projectname in $configPath");
	$project = new Project($projectname, $configPath);

	return $project;
    }

    /**
     * @param string $projectname The project name
     * @param OutputInterface $output The command output stream
     *
     * @return \ArrayObject
     */
    public function loadProjectConfig($projectname, OutputInterface $output)
    {
	$filesystem = $this->app['filesystem'];
	/* @var $filesystem FileSystemProvider */
	$configPath = $filesystem->getProjectConfigDirectory($projectname) . "config.xml";
	OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "Loading the project config from " . $configPath);
	$config = new \ArrayObject();
	$xml = simplexml_load_file($configPath);
	foreach ($xml->var as $var) {
	    $tag = (string)$var["name"];
	    switch ($tag) {
		case "project.skeletons":
		    foreach ($var->item as $skel) {
			$config["skeletons"][] = (string)$skel["value"];
		    }
		    break;
		case "project.aliases":
		    foreach ($var->item as $alias) {
			$config["aliases"][] = (string)$alias["value"];
		    }
		    break;
		default:
		    $config[str_replace("project.", "", $tag)] = (string)$var["value"];
	    }
	}
	return $config;
    }

    /**
     * @param \ArrayObject $project The project
     * @param OutputInterface $output The command output stream
     *
    <?xml version="1.0" ?><config>
    <var name="project.skeletons">
    <item value="base"/>
    </var>
    </config>
     */
    public function writeProjectConfig($project, OutputInterface $output)
    {
	/* @var $filesystem FileSystemProvider */
	$filesystem = $this->app['filesystem'];
	$configPath = $filesystem->getProjectConfigDirectory($project["name"]) . "config.xml";
	OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "Writing the project config to " . $configPath);

	$config = new \SimpleXMLElement('<?xml version="1.0" ?><config></config>');

	/* @var $skeletonProvider SkeletonProvider */
	$skeletonProvider = $this->app['skeleton'];
	foreach ($project["skeletons"] as $skeletonname) {
	    $skeleton = $skeletonProvider->findSkeleton($skeletonname, $output);
	    $config = $skeleton->writeConfig($this->app, $project, $config, $output);
	}
	$dom = dom_import_simplexml($config)->ownerDocument;
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	file_put_contents($configPath, $dom->saveXML());
    }

    /**
     * @param \SimpleXMLElement $node
     * @param string $name
     * @param string $value
     * @return \SimpleXMLElement
     */
    public function addVar(\SimpleXMLElement $node, $name, $value){
	$var = $node->addChild('var');
	$var->addAttribute("name", "project.".$name);
	$var->addAttribute("value", $value);
	return $node;
    }

}