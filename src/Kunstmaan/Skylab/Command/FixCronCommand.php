<?php
namespace Kunstmaan\Skylab\Command;

use Kunstmaan\Skylab\Skeleton\AbstractSkeleton;
use Kunstmaan\Skylab\Skeleton\AnacronSkeleton;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * FixCronCommand
 */
class FixCronCommand extends AbstractCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->addDefaults()
            ->setName('cron')
            ->setDescription('Installs the cronjob on all or one Skylab projects')
            ->addArgument('project', InputArgument::OPTIONAL, 'If set, the task will only set the cron for the project named')
            ->setHelp(<<<EOT
The <info>cron</info> command will setup the cronjobs for one or all projects.

<info>php skylab.phar cron</info>                         # Will setup the cron for all projects
<info>php skylab.phar cron myproject</info>               # Will setup the cron the myproject project

EOT
            );
    }

    protected function doExecute()
    {
        $onlyprojectname = $this->input->getArgument('project');
        /** @var AnacronSkeleton $theSkeleton */
        $theSkeleton = $this->skeletonProvider->findSkeleton(AnacronSkeleton::NAME);
        $this->fileSystemProvider->projectsLoop(function ($project) use ($onlyprojectname, $theSkeleton) {
            if (isset($onlyprojectname) && $project["name"] != $onlyprojectname) {
                return;
            }
            if ($this->skeletonProvider->hasSkeleton($project, $theSkeleton)) {
                $this->dialogProvider->logStep("Running cron on project " . $project["name"]);
                $theSkeleton->maintenance($project);
            }
        });
    }
}
