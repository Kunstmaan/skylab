<?php
namespace Kunstmaan\Skylab\Command;

use Kunstmaan\Skylab\Helper\OutputUtil;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

/**
 * MaintenanceCommand
 */
class MaintenanceCommand extends AbstractCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
    $this
        ->setName('maintenance')
        ->setDescription('Run maintenance on all Skylab projects')
        ->addOption("--hideLogo", null, InputOption::VALUE_NONE, 'If set, no logo or statistics will be shown');
    }

    /**
     * @param InputInterface  $input  The command inputstream
     * @param OutputInterface $output The command outputstream
     *
     * @return int|void
     * @throws \RuntimeException
     */
    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
    $projects = $this->filesystem->getProjects();
    foreach ($projects as $projectFile) {
        /** @var $projectFile SplFileInfo */
        $projectname = $projectFile->getFilename();
        OutputUtil::logStep($output, OutputInterface::VERBOSITY_NORMAL, "Running maintenance on project $projectname");
        $project = $this->projectConfig->loadProjectConfig($projectname, $output);
        foreach ($project["skeletons"] as $skeleton) {
        OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Skeleton: <info>$skeleton</info>");
        $skeleton = $this->skeleton->findSkeleton($skeleton, $output);
        if ($skeleton) {
            $skeleton->maintenance($this->getContainer(), $project, $output);
        }
        OutputUtil::newLine($output);
        }
    }

    }
}
