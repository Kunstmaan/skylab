<?php
namespace Kunstmaan\Skylab\Command;

use Kunstmaan\Skylab\Skeleton\AbstractSkeleton;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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

    protected function doExecute()
    {
        $onlyprojectname = $this->input->getArgument('project');
        $this->fileSystemProvider->projectsLoop(function ($project) use ($onlyprojectname) {
            if (isset($onlyprojectname) && $project["name"] != $onlyprojectname) {
                return;
            }
            $this->dialogProvider->logStep("Running backup on project " . $project["name"]);
            $this->skeletonProvider->skeletonLoop(function (AbstractSkeleton $theSkeleton) use ($project) {
                $this->dialogProvider->logTask("Running preBackup for skeleton " . $theSkeleton->getName());
                $theSkeleton->preBackup($project);
            });
            if (!$this->input->getOption('quick')) {
                $this->dialogProvider->logTask("Tarring the project folder");
                $this->fileSystemProvider->runTar($project, $this->output);
            }
            $this->skeletonProvider->skeletonLoop(function (AbstractSkeleton $theSkeleton) use ($project) {
                $this->dialogProvider->logTask("Running postBackup for skeleton " . $theSkeleton->getName());
                $theSkeleton->postBackup($project);
            });
        });
    }
}
