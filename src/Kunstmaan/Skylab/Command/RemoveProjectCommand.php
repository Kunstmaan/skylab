<?php
namespace Kunstmaan\Skylab\Command;

use Kunstmaan\Skylab\Skeleton\AbstractSkeleton;
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
            ->addDefaults()
            ->setName('remove')
            ->setDescription('Removes a Skylab project')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the project.')
            ->addOption("force", null, InputOption::VALUE_NONE, 'Does not ask before removing')
            ->addOption('no-backup', null, InputOption::VALUE_NONE, 'Removes the project without making a backup first')
            ->setHelp(<<<EOT
The <info>remove</info> command will remove the project after creating a backup first.

<info>php skylab.phar remove testproject</info>                         # Will remove the testproject project
<info>php skylab.phar remove testproject --force</info>                 # Will do the same, but don't ask you if you are sure.

EOT
            );
    }

    protected function doExecute()
    {
        $projectname = $this->dialogProvider->askFor("Please enter the name of the project", 'name');

        // Check if the project exists, do use in creating a new one with the same name.
        if (!$this->fileSystemProvider->projectExists($projectname)) {
            $this->dialogProvider->logError("A project with name $projectname does not exists!", false);
        }

        if (!$this->input->getOption('force') && !$this->dialogProvider->askConfirmation('Are you sure you want to remove ' . $projectname . '?')) {
            return;
        }

        $this->dialogProvider->logStep("Removing project $projectname");

        if (!$this->input->getOption('no-backup')) {
            $command = $this->getApplication()->find('backup');
            $arguments = array(
                'command' => 'backup',
                'project' => $projectname,
                '--hideLogo' => true
            );
            $input = new ArrayInput($arguments);
            $returnCode = $command->run($input, $this->output);
        } else {
            $returnCode = 0;
        }

        if (!$returnCode) {
            $this->permissionsProvider->killProcesses($projectname);
        }

        $project = $this->projectConfigProvider->loadProjectConfig($projectname);

        $this->skeletonProvider->skeletonLoop(function (AbstractSkeleton $skeleton) use ($project) {
            $this->dialogProvider->logTask("Running preRemove for skeleton " . $skeleton->getName());
            $skeleton->preRemove($project);
        }, new \ArrayObject($project["skeletons"]));

        $this->dialogProvider->logTask("Deleting the project folder at " . $this->fileSystemProvider->getProjectDirectory($projectname));
        $this->fileSystemProvider->removeProjectDirectory($project);

        $this->skeletonProvider->skeletonLoop(function (AbstractSkeleton $skeleton) use ($project) {
            $this->dialogProvider->logTask("Running postRemove for skeleton " . $skeleton->getName());
            $skeleton->postRemove($project);
        }, new \ArrayObject($project["skeletons"]));
    }
}
