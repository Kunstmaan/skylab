<?php
namespace Kunstmaan\Skylab\Provider;

use Cilex\ServiceProviderInterface;
//use Kunstmaan\kServer\Entity\Project;
use Symfony\Component\Finder\Finder;
use Cilex\Application;
//use Symfony\Component\Console\Output\OutputInterface;
//use Kunstmaan\kServer\Provider\ProcessProvider;

/**
 * FileSystemProvider
 */
class FileSystemProvider extends AbstractProvider implements ServiceProviderInterface
{

    /**
     * @var Application
     */
    private $app;

//    /**
//     * @var ProcessProvider
//     */
//    private $process;

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
     * @return array
     */
    public function getProjects()
    {
        $finder = new Finder();
        $finder->directories()->sortByName()->in($this->app["config"]["projects"]["path"])->depth('== 0');
        return iterator_to_array($finder);
    }

//    /**
//     * @param string $projectname
//     *
//     * @return bool
//     */
//    public function projectExists($projectname)
//    {
//        $finder = new Finder();
//        $finder->directories()->sortByName()->in($this->app["config"]["projects"]["path"])->depth('== 0')->name($projectname);
//
//        return $finder->count() != 0;
//    }
//
//    /**
//     * @param string $projectname
//     *
//     * @return string
//     */
//    public function getProjectDirectory($projectname)
//    {
//        return $this->app["config"]["projects"]["path"] . '/' . $projectname;
//    }
//
//    /**
//     * @param string $projectname
//     *
//     * @return string
//     */
//    public function getProjectConfigDirectory($projectname)
//    {
//        return $this->getProjectDirectory($projectname)."/current/kconfig";
//    }
//
//    /**
//     * @param string          $projectname The project name
//     * @param OutputInterface $output      The command output stream
//     */
//    public function createProjectDirectory($projectname, OutputInterface $output)
//    {
//        $projectDirectory = $this->getProjectDirectory($projectname);
//        if (is_null($this->process)) {
//            $this->process = $this->app["process"];
//        }
//        $this->process->executeCommand('mkdir -p ' . $projectDirectory, $output);
//    }
//
//    /**
//     * @param Project         $project The project
//     * @param OutputInterface $output  The command output stream
//     */
//    public function createProjectConfigDirectory(Project $project, OutputInterface $output)
//    {
//        $projectDirectory = $this->getProjectDirectory($project->getName());
//        if (is_null($this->process)) {
//            $this->process = $this->app["process"];
//        }
//        $this->process->executeCommand('mkdir -p ' . $projectDirectory . '/working-copy/kconfig', $output);
//    }
//
//    /**
//     * @param Project         $project The project
//     * @param OutputInterface $output  The command output stream
//     * @param string          $path    The relative path in the project folder
//     */
//    public function createDirectory(Project $project, OutputInterface $output, $path)
//    {
//        $projectDirectory = $this->getProjectDirectory($project->getName());
//        if (is_null($this->process)) {
//            $this->process = $this->app["process"];
//        }
//        $this->process->executeCommand('mkdir -p ' . $projectDirectory . '/' . $path, $output);
//    }
//
//    /**
//     * @param Project         $project The project
//     * @param OutputInterface $output  The command output stream
//     * @param string          $path    The relative path in the project folder
//     *
//     * @return string
//     */
//    public function getDirectory(Project $project, OutputInterface $output, $path)
//    {
//        $projectDirectory = $this->getProjectDirectory($project->getName());
//        if (is_null($this->process)) {
//            $this->process = $this->app["process"];
//        }
//
//        return  $projectDirectory . '/' . $path;
//    }
//
//    /**
//     * @param Project         $project The project
//     * @param OutputInterface $output  The command output stream
//     */
//    public function runTar(Project $project, OutputInterface $output)
//    {
//        if (is_null($this->process)) {
//            $this->process = $this->app["process"];
//        }
//        $this->process->executeCommand('mkdir -p ' . $this->app["config"]["projects"]["backuppath"], $output);
//        $projectDirectory = $this->getProjectDirectory($project->getName());
//        $excluded = '';
//        if (!is_null($project->getExcludedFromBackup())) {
//            foreach ($project->getExcludedFromBackup() as $excl) {
//                $excluded = $excluded . " --exclude='" . $excl . "'";
//            }
//        }
//        $this->process->executeCommand('nice -n 19 tar --create --absolute-names ' . $excluded . ' --file ' . $this->app["config"]["projects"]["backuppath"] . '/' . $project->getName() . '.tar.gz --totals --gzip ' . $projectDirectory . '/ 2>&1', $output);
//    }
//
//    /**
//     * @param Project         $project The project
//     * @param OutputInterface $output  The command output stream
//     */
//    public function removeProjectDirectory(Project $project, OutputInterface $output)
//    {
//        $projectDirectory = $this->getProjectDirectory($project->getName());
//        if (is_null($this->process)) {
//            $this->process = $this->app["process"];
//        }
//        $this->process->executeCommand("rm -Rf " . $projectDirectory, $output);
//    }
//
//    /**
//     * @param Project         $project The project
//     * @param OutputInterface $output  The command output stream
//     */
//    public function createCompiledVhostConfigDirectory(Project $project, OutputInterface $output)
//    {
//        if (is_null($this->process)) {
//            $this->process = $this->app["process"];
//        }
//        $this->process->executeCommand('mkdir -p /etc/apache2/vhost.d/' . $project->getName(), $output);
//        $this->process->executeCommand('mkdir -p /etc/apache2/vhost.d/' . $project->getName() . "/shared", $output);
//        $this->process->executeCommand('mkdir -p /etc/apache2/vhost.d/' . $project->getName() . "/nossl", $output);
//        $this->process->executeCommand('mkdir -p /etc/apache2/vhost.d/' . $project->getName() . "/ssl", $output);
//    }
//
//    /**
//     * @param Project         $project The project
//     * @param OutputInterface $output  The command output stream
//     *
//     * @return string
//     */
//    public function getCompiledVhostConfigDirectory(Project $project, OutputInterface $output)
//    {
//        return '/etc/apache2/vhost.d/' . $project->getName();
//    }
}