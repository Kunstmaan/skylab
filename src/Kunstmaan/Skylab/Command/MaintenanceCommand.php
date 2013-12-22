<?php
namespace Kunstmaan\Skylab\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * MaintenanceCommand
 */
class MaintenanceCommand extends AbstractCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('maintenance')
            ->setDescription('Run maintenance on all Skylab projects')
            ->addOption("--hideLogo", null, InputOption::VALUE_NONE, 'If set, no logo or statistics will be shown');
    }

    /**
     * @param InputInterface $input The command inputstream
     * @param OutputInterface $output The command outputstream
     *
     * @return int|void
     * @throws \RuntimeException
     */
    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getContainer();
        $allSkeletons = new \ArrayObject(array_keys($app["config"]["skeletons"]));
        $this->skeleton->skeletonLoop('preMaintenance', $allSkeletons, "Running preMaintenance for all skeletons", $app, $output);
        $this->filesystem->projectsLoop($output, function ($projectName, $skeletons, $project) use ($app, $output) {
            $this->skeleton->skeletonLoop('maintenance', $skeletons, "Running maintenance on project $projectName", $app, $output, $project);
        });
        $this->skeleton->skeletonLoop('postMaintenance', $allSkeletons, "Running postMaintenance for all skeletons", $app, $output);
    }
}
