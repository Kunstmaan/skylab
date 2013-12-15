<?php
namespace Kunstmaan\Skylab\Command;

use Kunstmaan\Skylab\Skeleton\SkeletonInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RemoveProjectCommand
 */
class RemoveProjectCommand extends AbstractCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
	$this
	    ->setName('remove')
	    ->setDescription('Removes a Skylab project')
	    ->addArgument('name', InputArgument::OPTIONAL, 'The name of the project.')
	    ->addArgument("--force", null, InputOption::VALUE_NONE, 'Does not ask before removing');
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
//        $projectname = $this->dialog->askFor('name', "Please enter the name of the project", $input, $output);
//
//        // Check if the project exists, do use in creating a new one with the same name.
//        if (!$this->filesystem->projectExists($projectname)) {
//            throw new RuntimeException("A project with name $projectname does not exist!");
//        }
//
//        /** @var $dialog DialogHelper */
//        $dialog = $this->getHelperSet()->get('dialog');
//        $forceArgument = $input->getArgument('--force');
//        if (empty($forceArgument) && !$dialog->askConfirmation($output, '<question>Are you sure you want to remove ' . $projectname . '?</question>', false)) {
//            return;
//        }
//
//        OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Removing project $projectname");
//
//        $command = $this->getApplication()->find('backup');
//        $arguments = array(
//            'command' => 'backup',
//            'project' => $projectname,
//        );
//        $input = new ArrayInput($arguments);
//        $returnCode = $command->run($input, $output);
//
//        if (is_null($returnCode)) {
//            //$this->permission->killProcesses($projectname, $output);
//        }
//
//        $project = $this->projectConfig->loadProjectConfig($projectname, $output);
//
//        // Run the preRemove hook for all dependencies
//        foreach ($project->getDependencies() as $skeletonName => $skeletonClass) {
//            OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "Running preRemove of the $skeletonName skeleton");
//            $skeleton = $this->skeleton->findSkeleton($skeletonName);
//            $skeleton->preRemove($this->getContainer(), $project, $output);
//        }
//
//        $this->filesystem->removeProjectDirectory($project, $output);
//
//        // Run the postRemove hook for all dependencies
//        foreach ($project->getDependencies() as $skeletonName => $skeletonClass) {
//            OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "Running postRemove of the $skeletonName skeleton");
//            $skeleton = $this->skeleton->findSkeleton($skeletonName);
//            $skeleton->postRemove($this->getContainer(), $project, $output);
//        }
    }
}