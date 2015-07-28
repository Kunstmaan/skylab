<?php
namespace Kunstmaan\Skylab\Provider;

use RuntimeException;
use Cilex\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * FileSystemProvider
 */
class FileSystemProvider extends AbstractProvider
{

    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['filesystem'] = $this;
        $this->app = $app;
    }

    /**
     * @return SplFileInfo[]
     */
    public function getProjects()
    {
        $finder = new Finder();
        $finder
            ->directories()
            ->sortByName()
            ->in($this->app["config"]["projects"]["path"])
            ->depth('== 0')
            ->filter(function (\SplFileInfo $file) {
                return file_exists($file->getRealPath() . "/conf/config.xml");
            });

        return iterator_to_array($finder);
    }

    /**
     * @param string $path
     * @return SplFileInfo[]
     */
    public function getDotDFiles($path)
    {
        $finder = new Finder();
        $finder->files()->sortByName()->in($path)->depth('== 0');

        return iterator_to_array($finder);
    }

    /**
     * @param  string $projectname
     * @return bool
     */
    public function projectExists($projectname)
    {
        $finder = new Finder();
        $finder->directories()->sortByName()->in($this->app["config"]["projects"]["path"])->depth('== 0')->name($projectname);

        return $finder->count() != 0;
    }

    /**
     * @param string $projectname
     *
     * @return string
     */
    public function getProjectConfigDirectory($projectname)
    {
        return $this->getProjectDirectory($projectname) . "/conf";
    }

    /**
     * @param string $projectname
     *
     * @return string
     */
    public function getProjectDirectory($projectname)
    {
        return $this->app["config"]["projects"]["path"] . '/' . $projectname;
    }

    /**
     * @param string $projectname The project name
     */
    public function createProjectDirectory($projectname)
    {
        $projectDirectory = $this->getProjectDirectory($projectname);
        $this->processProvider->executeSudoCommand('mkdir -p ' . $projectDirectory);
    }

    /**
     * @param \ArrayObject $project The project
     * @param string       $path    The relative path in the project folder
     */
    public function createDirectory(\ArrayObject $project, $path)
    {
        $projectDirectory = $this->getProjectDirectory($project["name"]);
        $this->processProvider->executeSudoCommand('mkdir -p ' . $projectDirectory . '/' . $path);
    }

    /**
     * @param \ArrayObject $project The project
     * @param string       $path    The relative path in the project folder
     *
     * @return string
     */
    public function getDirectory(\ArrayObject $project, $path)
    {
        $projectDirectory = $this->getProjectDirectory($project["name"]);

        return $projectDirectory . '/' . $path;
    }

    /**
     * @param \ArrayObject $project The project
     */
    public function runTar(\ArrayObject $project)
    {
        $this->processProvider->executeSudoCommand('mkdir -p ' . $this->app["config"]["projects"]["backuppath"]);
        $projectDirectory = $this->getProjectDirectory($project["name"]);
        $excluded = '';
        foreach ($project["backupexcludes"] as $backupexclude) {
            $excluded = $excluded . " --exclude='" . $backupexclude . "'";
        }
        $this->processProvider->executeSudoCommand('nice -n 19 tar --create --absolute-names ' . $excluded . ' --file ' . $this->app["config"]["projects"]["backuppath"] . '/' . $project["name"] . '.tar.gz --totals --gzip ' . $projectDirectory . '/ 2>&1');
    }

    /**
     * @param \ArrayObject $project The project
     */
    public function removeProjectDirectory(\ArrayObject $project)
    {
        if(empty($project["dir"])) {
            throw new RuntimeException("The project has no name");
        }
        $projectDirectory = $this->getProjectDirectory($project["name"]);
        $this->processProvider->executeSudoCommand("rm -Rf " . $projectDirectory);
    }

    /**
     * @return string
     */
    public function getApacheConfigTemplateDir($clean=false)
    {
	return $this->getConfigTemplateDir("apache", $clean);
    }

    /**
     * @return string
     */
    public function getConfigTemplateDir($skeletonName, $clean=false)
    {
	return ($clean ? "/".$skeletonName."/apache.d/" : BASE_DIR . "/templates/".$skeletonName."/apache.d/");
    }

    /**
     * @return string
     */
    public function getNginxConfigTemplateDir($clean=false)
    {
        return ($clean?"/nginx/nginx.d/":BASE_DIR . "/templates/nginx/nginx.d/");
    }

    /**
     * @param \Closure $callback
     */
    public function projectsLoop($callback)
    {
        $projects = $this->getProjects();
        foreach ($projects as $projectFile) {
            /** @var $projectFile SplFileInfo */
            $projectname = $projectFile->getFilename();
            $project = $this->projectConfigProvider->loadProjectConfig($projectname);
            $callback($project);
        }
    }

    /**
     * @param  \ArrayObject $project
     * @return array
     */
    public function getProjectApacheConfigs(\ArrayObject $project)
    {
        $finder = new Finder();
        $finder->files()
            ->sortByName()
            ->in($this->getProjectConfigDirectory($project["name"]) . "/apache.d/")
            ->ignoreVCS(true)
            ->ignoreDotFiles(true)
            ->notName("*~")
            ->notName("*.swp")
            ->notName("*.bak")
            ->notName("*-")
            ->depth('== 0');

        return iterator_to_array($finder);
    }

    /**
     * @param  \ArrayObject $project
     * @return array
     */
    public function getProjectNginxConfigs(\ArrayObject $project)
    {
        $finder = new Finder();
        $finder->files()
            ->sortByName()
            ->in($this->getProjectConfigDirectory($project["name"]) . "/nginx.d/")
            ->ignoreVCS(true)
            ->ignoreDotFiles(true)
            ->notName("*~")
            ->notName("*.swp")
            ->notName("*.bak")
            ->notName("*-")
            ->depth('== 0');

        return iterator_to_array($finder);
    }
    /**
     * @param $path
     * @param $content
     */
    public function writeProtectedFile($path, $content)
    {
        $tmpfname = tempnam(sys_get_temp_dir(), "skylab");
        file_put_contents($tmpfname, $content);
        $this->processProvider->executeSudoCommand("cat " . $tmpfname . " | sudo tee " . $path);
    }

    /**
     * @param string   $sourcePath
     * @param string   $destinationPath
     * @param string[] $variables
     */
    public function render($sourcePath, $destinationPath, $variables)
    {
        $this->dialogProvider->logConfig("Rendering " . $sourcePath . " to " . $destinationPath);
        $this->processProvider->executeSudoCommand('mkdir -p ' . dirname($destinationPath));
        $content = $this->renderString(file_get_contents(BASE_DIR . "/templates" . $sourcePath), $variables);
        $this->writeProtectedFile($destinationPath, $content);
    }

    /**
     * @param string   $sourcePath
     * @param string   $destinationPath
     * @param string[] $variables
     */
    public function renderDist($sourcePath, $destinationPath)
    {
        $this->dialogProvider->logConfig("Dist rendering " . $sourcePath . " to " . $destinationPath);
        $this->processProvider->executeSudoCommand('mkdir -p ' . dirname($destinationPath));
        $this->writeProtectedFile($destinationPath, $sourcePath);
    }

    /**
     * @param $content
     * @param $variables
     * @return string
     */
    public function renderString($content, $variables)
    {
        return $this->twig->render($content, $variables);
    }

    /**
     * @param $cleanedLocation
     * @param $target
     * @param SplFileInfo $config
     */
    public function renderSingleConfig($cleanedLocation, $target, $config)
    {
	$this->fileSystemProvider->renderDist(
	    $cleanedLocation . $config->getFilename(),
	    $target . str_replace(".conf.twig", ".dist", $config->getFilename())
	);
    }

    /**
     * @param $cleanedLocation
     * @param $target
     * @param SplFileInfo $config
     */
    public function renderSingleDistConfig($cleanedLocation, $target, $config)
    {
	$this->fileSystemProvider->renderDist(
	    $cleanedLocation . $config->getFilename(),
	    $target . str_replace(".conf.twig", ".dist", $config->getFilename())
	);
    }

    /**
     * @param $location
     * @param $cleanedLocation
     * @param $target
     */
    public function renderConfig($location, $cleanedLocation, $target)
    {
	// render templates
	$finder = new Finder();
	$finder->files()->in($location)->name("*.conf.twig");
	foreach ($finder as $config) {
	    $this->renderSingleConfig($cleanedLocation, $target, $config);
	}
    }

    /**
     * @param $location
     * @param $cleanedLocation
     * @param $target
     */
    public function renderDistConfig($location, $cleanedLocation, $target)
    {
	// render templates
	$finder = new Finder();
	$finder->files()->in($location)->name("*.conf.twig");
	foreach ($finder as $config) {
	    $this->renderSingleDistConfig($cleanedLocation, $target, $config);
	}
    }
}
