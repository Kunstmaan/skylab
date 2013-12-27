<?php
namespace Kunstmaan\Skylab\Command;

use Kunstmaan\Skylab\Skeleton\AbstractSkeleton;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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


    protected function doExecute()
    {
        $projectname = $this->dialogProvider->askFor("Please enter the name of the project", 'name');

        // Check if the project exists, do use in creating a new one with the same name.
        if (!$this->fileSystemProvider->projectExists($projectname)) {
            throw new RuntimeException("A project with name $projectname does not exist!");
        }

        if (!$this->input->getOption('force') && !$this->dialogProvider->askConfirmation('Are you sure you want to remove ' . $projectname . '?')) {
            return;
        }

        $this->dialogProvider->logStep("Removing project $projectname");

        $command = $this->getApplication()->find('backup');
        $arguments = array(
            'command' => 'backup',
            'project' => $projectname,
            '--hideLogo' => true
        );
        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $this->output);

        if (is_null($returnCode)) {
            $this->permissionsProvider->killProcesses($projectname, $this->output);
        }

        $project = $this->projectConfigProvider->loadProjectConfig($projectname, $this->output);

        $this->skeletonProvider->skeletonLoop(function (AbstractSkeleton $skeleton) use ($project) {
            $skeleton->preRemove($project);
        });


        $this->dialogProvider->logTask("Deleting the project folder at " . $this->fileSystemProvider->getProjectDirectory($projectname));
        $this->fileSystemProvider->removeProjectDirectory($project, $this->output);

        $this->skeletonProvider->skeletonLoop(function (AbstractSkeleton $skeleton) use ($project) {
            $skeleton->postRemove($project);
        });
    }
}
