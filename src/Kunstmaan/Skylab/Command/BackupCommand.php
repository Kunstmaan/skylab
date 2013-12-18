<?php
namespace Kunstmaan\Skylab\Command;

use Kunstmaan\Skylab\Helper\OutputUtil;
use Kunstmaan\Skylab\Skeleton\SkeletonInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * BackupCommand
 */
class BackupCommand extends AbstractCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
	$this
	    ->setName('backup')
	    ->setDescription('Run backup on all or one Skylab projects')
	    ->addArgument('project', InputArgument::OPTIONAL, 'If set, the task will only backup the project named')
	    ->addOption("--quick", null, InputOption::VALUE_NONE, 'If set, no tar.gz file will be created, only the preBackup and postBackup hooks will be executed.')
	    ->addOption("--hideLogo", null, InputOption::VALUE_NONE, 'If set, no logo or statistics will be shown');
    }

    /**
     * @param InputInterface $input The command inputstream
     * @param OutputInterface $output The command outputstream
     *
     * @return int|void
     *
     * @throws \RuntimeException
     */
    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
	$onlyprojectname = $input->getArgument('project');

	// Loop over all the projects to run the backup
	$projects = $this->filesystem->getProjects();

	/** @var $projectFile \SplFileInfo */
	foreach ($projects as $projectFile) {

	    // Check if the user wants to run the backup of only one project
	    $projectname = $projectFile->getFilename();
	    if (isset($onlyprojectname) && $projectname != $onlyprojectname) {
		continue;
	    }

	    OutputUtil::logStep($output, OutputInterface::VERBOSITY_NORMAL, "Running backup on project $projectname");
	    $project = $this->projectConfig->loadProjectConfig($projectname, $output);

	    // Run the preBackup hook for all dependencies
	    foreach ($project["skeletons"] as $skeleton) {
		OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "preBackup for skeleton: <info>$skeleton</info>");
		$skeleton = $this->skeleton->findSkeleton($skeleton, $output);
		if ($skeleton){
		    $skeleton->preBackup($this->getContainer(), $project, $output);
		}
		OutputUtil::newLine($output);
	    }

	    if (!$input->getOption('quick')) {
		OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Tarring the project folder");
		// Create the tar.gz file of the project directory
		$this->filesystem->runTar($project, $output);
		OutputUtil::newLine($output);
	    }

	    // Run the postBackup hook for all dependencies
	    foreach ($project["skeletons"] as $skeleton) {
		OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "postBackup for skeleton: <info>$skeleton</info>");
		$skeleton = $this->skeleton->findSkeleton($skeleton, $output);
		if ($skeleton){
		    $skeleton->postBackup($this->getContainer(), $project, $output);
		}
		OutputUtil::newLine($output);
	    }
	}
    }
}