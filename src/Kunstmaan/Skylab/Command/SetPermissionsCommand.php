<?php
namespace Kunstmaan\Skylab\Command;

use Kunstmaan\Skylab\Skeleton\BaseSkeleton;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SetPermissionsCommand
 */
class SetPermissionsCommand extends AbstractCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('permissions')
            ->setDescription('Set the permissions of a kServer project')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the project')
            ->addOption("--hideLogo", null, InputOption::VALUE_NONE, 'If set, no logo or statistics will be shown');
    }

    /**
     * @throws \RuntimeException
     */
    protected function doExecute()
    {
        $projectname = $this->input->getArgument('name');

        if (!$this->fileSystemProvider->projectExists($projectname)) {
            throw new RuntimeException("The $projectname project does not exist.");
        }

        $this->dialogProvider->logStep($this->output, OutputInterface::VERBOSITY_NORMAL, "Setting permissions on project $projectname");

        /** @var BaseSkeleton $baseSkeleton */
        $baseSkeleton = $this->skeletonProvider->findSkeleton("base", $this->output);
        $project = $this->projectConfigProvider->loadProjectConfig($projectname, $this->output);
        $baseSkeleton->setPermissions($this->getContainer(), $project, $this->output, true);
    }
}
