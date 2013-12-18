<?php

namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Cilex\ServiceProviderInterface;
use Kunstmaan\Skylab\Entity\PermissionDefinition;
use Kunstmaan\Skylab\Helper\OutputUtil;
use Symfony\Component\Console\Output\OutputInterface;

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
	OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Loading <info>configuration files</info>");
	$config = new \ArrayObject();
	$config = $this->loadConfig($projectname, $output, $config);
	$config = $this->loadOwnership($projectname, $output, $config);
	$config = $this->loadPermissions($projectname, $output, $config);
	$config = $this->loadBackup($projectname, $output, $config);
	OutputUtil::newLine($output);
	return $config;
    }

    /**
     * @param $projectname
     * @param OutputInterface $output
     * @param $config
     * @return \ArrayObject
     */
    private function loadConfig($projectname, OutputInterface $output, \ArrayObject $config)
    {
	/* @var $filesystem FileSystemProvider */
	$filesystem = $this->app['filesystem'];
	$configPath = $filesystem->getProjectConfigDirectory($projectname) . "config.xml";
	OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "%", "Loading the project config from " . $configPath);

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
     * @param $projectname
     * @param OutputInterface $output
     * @param \ArrayObject $config
     * @return \ArrayObject
     */
    private function loadOwnership($projectname, OutputInterface $output, \ArrayObject $config)
    {
	/* @var $filesystem FileSystemProvider */
	$filesystem = $this->app['filesystem'];
	$configPath = $filesystem->getProjectConfigDirectory($projectname) . "ownership.xml";
	OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "%", "Loading the project ownership from " . $configPath);

	$xml = simplexml_load_file($configPath);
	foreach ($xml->var as $var) {
	    $name = (string)$var["name"];
	    $value = (string)$var["value"];
	    if (isset($project["permissions"][$name])) {
		$permissionDefinition = $config["permissions"][$name];
	    } else {
		$permissionDefinition = new PermissionDefinition();
	    }
	    $permissionDefinition->setPath($name);
	    $permissionDefinition->setOwnership($value);
	    $config["permissions"][$name] = $permissionDefinition;
	}
	return $config;
    }

    /**
     * @param string $value
     * @param \ArrayObject $config
     * @return string
     */
    public function searchReplacer($value, \ArrayObject $config){
	$replaceDictionary = new \ArrayObject(array(
	    "config.superuser" => $this->app["config"]["users"]["superuser"],
	    "config.supergroup" => $this->app["config"]["users"]["supergroup"],
	    "config.wwwuser" => $this->app["config"]["users"]["wwwuser"],
	    "project.group" => $config["name"],
	    "project.user" => $config["name"],
	));

	preg_match_all("/@(\w*?\.\w*?)@/", $value, $hits);
	foreach ($hits[0] as $index => $hit){
	    $value = str_replace($hit, $replaceDictionary[$hits[1][$index]], $value);
	}

	return $value;
    }

    /**
     * @param $projectname
     * @param OutputInterface $output
     * @param \ArrayObject $config
     * @return \ArrayObject
     */
    private function loadPermissions($projectname, OutputInterface $output, \ArrayObject $config)
    {
	/* @var $filesystem FileSystemProvider */
	$filesystem = $this->app['filesystem'];
	$configPath = $filesystem->getProjectConfigDirectory($projectname) . "permissions.xml";
	OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "%", "Loading the project permissions from " . $configPath);

	$xml = simplexml_load_file($configPath);
	foreach ($xml->var as $var) {
	    $name = (string)$var["name"];
	    if (isset($config["permissions"][$name])) {
		$permissionDefinition = $config["permissions"][$name];
	    } else {
		$permissionDefinition = new PermissionDefinition();
	    }
	    $permissionDefinition->setPath($name);
	    foreach ($var->item as $item) {
		$value = (string)$item["value"];
		$permissionDefinition->addAcl($value);
	    }
	    $config["permissions"][$name] = $permissionDefinition;
	}
	return $config;
    }

    /**
     * @param $projectname
     * @param OutputInterface $output
     * @param \ArrayObject $config
     * @return \ArrayObject
     */
    private function loadBackup($projectname, OutputInterface $output, \ArrayObject $config)
    {
	/* @var $filesystem FileSystemProvider */
	$filesystem = $this->app['filesystem'];
	$configPath = $filesystem->getProjectConfigDirectory($projectname) . "backup.xml";
	OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "%", "Loading the project backup excludes from " . $configPath);
	$xml = simplexml_load_file($configPath);
	foreach ($xml->var[0]->item as $item) {
	    $value = (string)$item["value"];
	    $config["backupexcludes"][$value] = $value;
	}
	return $config;
    }

    /**
     * @param \ArrayObject $project The project
     * @param OutputInterface $output The command output stream
     */
    public function writeProjectConfig(\ArrayObject $project, OutputInterface $output)
    {
	OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Writing <info>configuration files</info>");
	$this->writeConfig($project, $output);
	$this->writeOwnership($project, $output);
	$this->writePermissions($project, $output);
	$this->writeBackup($project, $output);
	OutputUtil::newLine($output);
    }

    /**
     * @param \ArrayObject $project
     * @param OutputInterface $output
     */
    private function writeConfig(\ArrayObject $project, OutputInterface $output)
    {
	/* @var $filesystem FileSystemProvider */
	$filesystem = $this->app['filesystem'];

	$configPath = $filesystem->getProjectConfigDirectory($project["name"]) . "config.xml";
	OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "%", "Writing the project config to " . $configPath);

	$config = new \SimpleXMLElement('<?xml version="1.0" ?><config></config>');
	/* @var $skeletonProvider SkeletonProvider */
	$skeletonProvider = $this->app['skeleton'];
	$skels = $config->addChild('var');
	$skels->addAttribute("name", "project.skeletons");
	foreach ($project["skeletons"] as $skeletonname) {
	    $this->addItem($skels, $skeletonname);
	    $skeleton = $skeletonProvider->findSkeleton($skeletonname, $output);
	    $config = $skeleton->writeConfig($this->app, $project, $config, $output);
	}
	$this->writeToFile($config, $configPath);
    }

    /**
     * @param \SimpleXMLElement $var
     * @param string $value
     * @return \SimpleXMLElement
     */
    public function addItem(\SimpleXMLElement $var, $value)
    {
	$item = $var->addChild('item');
	$item->addAttribute("value", $value);
	return $var;
    }

    /**
     * @param $xml
     * @param $path
     */
    private function writeToFile($xml, $path)
    {
	$dom = dom_import_simplexml($xml)->ownerDocument;
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	file_put_contents($path, $dom->saveXML());
    }

    /**
     * @param \ArrayObject $project
     * @param OutputInterface $output
     */
    private function writeOwnership(\ArrayObject $project, OutputInterface $output)
    {
	/* @var $filesystem FileSystemProvider */
	$filesystem = $this->app['filesystem'];

	$ownershipPath = $filesystem->getProjectConfigDirectory($project["name"]) . "ownership.xml";
	OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "%", "Writing the project's ownership config to " . $ownershipPath);

	$ownership = new \SimpleXMLElement('<?xml version="1.0" ?><config></config>');
	/** @var PermissionDefinition $permission */
	foreach ($project["permissions"] as $permission) {
	    $ownership = $this->addVar($ownership, $permission->getPath(), $permission->getOwnership());
	}
	$this->writeToFile($ownership, $ownershipPath);
    }

    /**
     * @param \SimpleXMLElement $node
     * @param string $name
     * @param string $value
     * @return \SimpleXMLElement
     */
    public function addVar(\SimpleXMLElement $node, $name, $value)
    {
	$var = $node->addChild('var');
	$var->addAttribute("name", $name);
	$var->addAttribute("value", $value);
	return $node;
    }

    /**
     * @param \ArrayObject $project
     * @param OutputInterface $output
     */
    private function writePermissions(\ArrayObject $project, OutputInterface $output)
    {
	/* @var $filesystem FileSystemProvider */
	$filesystem = $this->app['filesystem'];

	$permissionsPath = $filesystem->getProjectConfigDirectory($project["name"]) . "permissions.xml";
	OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "%", "Writing the project's permissions config to " . $permissionsPath);

	$permissions = new \SimpleXMLElement('<?xml version="1.0" ?><config></config>');
	/** @var PermissionDefinition $permission */
	foreach ($project["permissions"] as $permission) {
	    $var = $permissions->addChild('var');
	    $var->addAttribute("name", $permission->getPath());
	    foreach ($permission->getAcl() as $acl) {
		$var = $this->addItem($var, $acl);
	    }
	}
	$this->writeToFile($permissions, $permissionsPath);
    }

    /**
     * @param \ArrayObject $project
     * @param OutputInterface $output
     */
    private function writeBackup(\ArrayObject $project, OutputInterface $output)
    {
	/* @var $filesystem FileSystemProvider */
	$filesystem = $this->app['filesystem'];
	$backupPath = $filesystem->getProjectConfigDirectory($project["name"]) . "backup.xml";
	OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "%", "Writing the project's backup excludes config to " . $backupPath);

	$backup = new \SimpleXMLElement('<?xml version="1.0" ?><config></config>');
	$var = $backup->addChild('var');
	$var->addAttribute("name", "backup.excludes");
	foreach ($project["backupexcludes"] as $backupexclude) {
	    $var = $this->addItem($var, $backupexclude);
	}
	$this->writeToFile($backup, $backupPath);
    }

}