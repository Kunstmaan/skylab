<?php
namespace Kunstmaan\Skylab\Command;

use Cilex\Command\Command;
use Kunstmaan\Skylab\Helper\OutputUtil;

use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

/**
 * MaintenanceCommand
 */
class MaintenanceCommand extends Command
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('maintenance')
            ->setDescription('Run maintenance on all projects');
    }

    /**
     * @param InputInterface  $input  The command inputstream
     * @param OutputInterface $output The command outputstream
     *
     * @return int|void
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        OutputUtil::logo($output, OutputInterface::VERBOSITY_NORMAL, "Executing the 'maintenance' command");

        $projects = $this->getService('filesystem')->getProjects();
        /** @var $projectFile SplFileInfo */
        foreach ($projects as $projectFile) {
            $projectname = $projectFile->getFilename();
            OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Running maintenance on project <info>$projectname</info>");
          /*  $project = $this->projectConfig->loadProjectConfig($projectname, $output);
            foreach ($project->getDependencies() as $skeletonName => $skeletonClass) {
                OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Running maintenance of the $skeletonName skeleton");
                $skeleton = $this->skeleton->findSkeleton($skeletonName);
                $skeleton->maintenance($this->getContainer(), $project, $output);
            }*/
        }
    }
}