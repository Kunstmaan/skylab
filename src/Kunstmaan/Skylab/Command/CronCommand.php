<?php
namespace Kunstmaan\Skylab\Command;

use Kunstmaan\Skylab\Skeleton\BaseSkeleton;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;

/**
 * CronCommand
 */
class CronCommand extends AbstractCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->addDefaults()
            ->setName('cron')
            ->setDescription('Updates the cron configuration of a Skylab project')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the project')
            ->setHelp(<<<EOT
The <info>cron</info> command will update the cron configuration of a project.

<info>php skylab.phar cron testproject</info>

EOT
            );
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

        $this->dialogProvider->logTask("Updating cron on project $projectname");

        /** @var BaseSkeleton $baseSkeleton */
        $baseSkeleton = $this->skeletonProvider->findSkeleton("anacron");
        $project = $this->projectConfigProvider->loadProjectConfig($projectname);
        $baseSkeleton->maintenance($project, true);
    }
}
