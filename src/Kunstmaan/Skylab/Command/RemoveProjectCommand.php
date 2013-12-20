<?php
namespace Kunstmaan\Skylab\Command;

use Kunstmaan\Skylab\Helper\OutputUtil;
use RuntimeException;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\ArrayInput;
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
            ->addOption("force", null, InputOption::VALUE_NONE, 'Does not ask before removing')
            ->addOption("hideLogo", null, InputOption::VALUE_NONE, 'If set, no logo or statistics will be shown');
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
        $projectname = $this->dialog->askFor('name', "Please enter the name of the project", $input, $output);

        // Check if the project exists, do use in creating a new one with the same name.
        if (!$this->filesystem->projectExists($projectname)) {
            throw new RuntimeException("A project with name $projectname does not exist!");
        }

        /** @var $dialog DialogHelper */
        $dialog = $this->getHelperSet()->get('dialog');
        $forceArgument = $input->getOption('force');
        if (!$forceArgument && !$dialog->askConfirmation($output, '<question>Are you sure you want to remove ' . $projectname . '? [y/n]</question> ', false)) {
            return;
        }

        OutputUtil::logStep($output, OutputInterface::VERBOSITY_NORMAL, "Removing project $projectname");

        $command = $this->getApplication()->find('backup');
        $arguments = array(
            'command' => 'backup',
            'project' => $projectname,
            '--hideLogo' => true
        );
        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);

        if (is_null($returnCode)) {
            $this->permission->killProcesses($projectname, $output);
        }

        $project = $this->projectConfig->loadProjectConfig($projectname, $output);

        // Run the preRemove hook for all dependencies
        foreach ($project["skeletons"] as $skeleton) {
            OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "preRemove for skeleton: <info>$skeleton</info>");
            $skeleton = $this->skeleton->findSkeleton($skeleton, $output);
            if ($skeleton) {
                $skeleton->preRemove($this->getContainer(), $project, $output);
            }
        }
        OutputUtil::newLine($output);

        OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Deleting the project folder at <info>" . $this->filesystem->getProjectDirectory($projectname) . "</info>");
        $this->filesystem->removeProjectDirectory($project, $output);
        OutputUtil::newLine($output);

        foreach ($project["skeletons"] as $skeleton) {
            OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "postRemove for skeleton: <info>$skeleton</info>");
            $skeleton = $this->skeleton->findSkeleton($skeleton, $output);
            if ($skeleton) {
                $skeleton->postRemove($this->getContainer(), $project, $output);
            }
        }
        OutputUtil::newLine($output);
    }
}
