<?php
namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Cilex\ServiceProviderInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * FileSystemProvider
 */
class FileSystemProvider implements ServiceProviderInterface
{

    /**
     * @var Application
     */
    private $app;
    /**
     * @var ProcessProvider
     */
    private $process;

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
        $finder->directories()->sortByName()->in($this->app["config"]["projects"]["path"])->depth('== 0');

        return iterator_to_array($finder);
    }

    /**
     * @param $path
     * @return SplFileInfo[]
     */
    public function getDotDFiles($path)
    {
        $finder = new Finder();
        $finder->files()->sortByName()->in($path)->depth('== 0');

        return iterator_to_array($finder);
    }

    /**
     * @param string $projectname
     *
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
     * @param OutputInterface $output The command output stream
     */
    public function createProjectDirectory($projectname, OutputInterface $output)
    {
        $projectDirectory = $this->getProjectDirectory($projectname);
        if (is_null($this->process)) {
            $this->process = $this->app["process"];
        }
        $this->process->executeSudoCommand('mkdir -p ' . $projectDirectory, $output);
    }

    /**
     * @param \ArrayObject $project The project
     * @param OutputInterface $output The command output stream
     * @param string $path The relative path in the project folder
     */
    public function createDirectory(\ArrayObject $project, OutputInterface $output, $path)
    {
        $projectDirectory = $this->getProjectDirectory($project["name"]);
        if (is_null($this->process)) {
            $this->process = $this->app["process"];
        }
        $this->process->executeSudoCommand('mkdir -p ' . $projectDirectory . '/' . $path, $output);
    }

    /**
     * @param \ArrayObject $project The project
     * @param OutputInterface $output The command output stream
     * @param string $path The relative path in the project folder
     *
     * @return string
     */
    public function getDirectory(\ArrayObject $project, OutputInterface $output, $path)
    {
        $projectDirectory = $this->getProjectDirectory($project["name"]);
        if (is_null($this->process)) {
            $this->process = $this->app["process"];
        }

        return $projectDirectory . '/' . $path;
    }

    /**
     * @param \ArrayObject $project The project
     * @param OutputInterface $output The command output stream
     */
    public function runTar(\ArrayObject $project, OutputInterface $output)
    {
        if (is_null($this->process)) {
            $this->process = $this->app["process"];
        }
        $this->process->executeSudoCommand('mkdir -p ' . $this->app["config"]["projects"]["backuppath"], $output);
        $projectDirectory = $this->getProjectDirectory($project["name"]);
        $excluded = '';
        foreach ($project["backupexcludes"] as $backupexclude) {
            $excluded = $excluded . " --exclude='" . $backupexclude . "'";
        }
        $this->process->executeSudoCommand('nice -n 19 tar --create --absolute-names ' . $excluded . ' --file ' . $this->app["config"]["projects"]["backuppath"] . '/' . $project["name"] . '.tar.gz --totals --gzip ' . $projectDirectory . '/ 2>&1', $output);
    }

    /**
     * @param \ArrayObject $project The project
     * @param OutputInterface $output The command output stream
     */
    public function removeProjectDirectory(\ArrayObject $project, OutputInterface $output)
    {
        $projectDirectory = $this->getProjectDirectory($project["name"]);
        if (is_null($this->process)) {
            $this->process = $this->app["process"];
        }
        $this->process->executeSudoCommand("rm -Rf " . $projectDirectory, $output);
    }

    /**
     * @param  \ArrayObject $project
     * @param  OutputInterface $output
     * @return string
     */
    public function getApacheConfigTemplateDir(\ArrayObject $project, OutputInterface $output)
    {
        return __DIR__ . "/../../../../templates/apache/apache.d/";
    }


    /**
     * @param OutputInterface $output
     * @param $callback
     */
    public function projectsLoop(OutputInterface $output, $callback)
    {
        /** @var ProjectConfigProvider $projectConfig */
        $projectConfig = $this->app["projectconfig"];
        $projects = $this->getProjects();
        foreach ($projects as $projectFile) {
            /** @var $projectFile SplFileInfo */
            $projectname = $projectFile->getFilename();
            $project = $projectConfig->loadProjectConfig($projectname, $output);
            $callback($project["name"], new \ArrayObject($project["skeletons"]), $project);
        }
    }

    /**
     * @param \ArrayObject $project
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
     * @param $path
     * @param $content
     * @param OutputInterface $output
     */
    public function writeProtectedFile($path, $content, OutputInterface $output){
        $tmpfname = tempnam(sys_get_temp_dir(), "skylab");
        file_put_contents($tmpfname, $content);
        if (is_null($this->process)) {
            $this->process = $this->app["process"];
        }
        $this->process->executeSudoCommand("cat ".$tmpfname." | sudo tee ".$path, $output);
    }
}
