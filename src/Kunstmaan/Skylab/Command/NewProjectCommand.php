<?php
namespace Kunstmaan\Skylab\Command;

use Kunstmaan\Skylab\Helper\OutputUtil;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * NewProjectCommand
 */
class NewProjectCommand extends AbstractCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
	$this
	    ->setName('new')
	    ->setDescription('Create a new Skylab project')
	    ->addArgument('name', InputArgument::OPTIONAL, 'The name of the project. All lowercase, no spaces or special characters. Keep it short, yet descriptive')
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
    protected function DoExecute(InputInterface $input, OutputInterface $output)
    {
	$projectname = $this->dialog->askFor('name', "Please enter the name of the project. All lowercase, no spaces or special characters. Keep it short, yet descriptive", $input, $output);
	OutputUtil::logStep($output, OutputInterface::VERBOSITY_NORMAL, "Creating project $projectname");
	// Check if the project exists, do use in creating a new one with the same name.
	if ($this->filesystem->projectExists($projectname)) {
	    throw new RuntimeException("A project with name $projectname already exists!");
	} else {
	    OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Creating project directory for <info>$projectname</info>");
	    $this->filesystem->createProjectDirectory($projectname, $output);
	}
	OutputUtil::newLine($output);
	$project = new \ArrayObject();
	$project["name"] = $projectname;
	$project["dir"] = $this->filesystem->getProjectDirectory($projectname);
	$this->skeleton->applySkeleton($project, $this->skeleton->findSkeleton("base", $output), $output);
	OutputUtil::newLine($output);
	$this->projectConfig->writeProjectConfig($project, $output);

    }
}