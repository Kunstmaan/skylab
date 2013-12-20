<?php
namespace Kunstmaan\Skylab\Command;

use Cilex\Command\Command;
use Kunstmaan\Skylab\Helper\OutputUtil;
use Kunstmaan\Skylab\Provider\DialogProvider;
use Kunstmaan\Skylab\Provider\FileSystemProvider;
use Kunstmaan\Skylab\Provider\PermissionsProvider;
use Kunstmaan\Skylab\Provider\ProcessProvider;
use Kunstmaan\Skylab\Provider\ProjectConfigProvider;
use Kunstmaan\Skylab\Provider\SkeletonProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{

    /**
     * @var FileSystemProvider
     */
    protected $filesystem;

    /**
     * @var ProjectConfigProvider
     */
    protected $projectConfig;

    /**
     * @var SkeletonProvider
     */
    protected $skeleton;

    /**
     * @var ProcessProvider
     */
    protected $process;

    /**
     * @var PermissionsProvider
     */
    protected $permission;

    /**
     * @var DialogProvider
     */
    protected $dialog;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem = $this->getService('filesystem');
        $this->projectConfig = $this->getService('projectconfig');
        $this->skeleton = $this->getService('skeleton');
        $this->process = $this->getService('process');
        $this->permission = $this->getService('permission');
        $this->dialog = $this->getService('dialog');

        if (!$input->getOption('hideLogo')) {
            OutputUtil::logo($output, OutputInterface::VERBOSITY_NORMAL, "Executing " . get_class($this));
        }

        $this->process->executeCommand('sudo -p "Please enter your sudo password: " -v', $output, true);

        if (defined('SKYLAB_DEV_WARNING_TIME') && $this->getName() !== 'self-update') {
            if (time() > SKYLAB_DEV_WARNING_TIME) {
                OutputUtil::logWarning($output, OutputInterface::VERBOSITY_NORMAL, 'Warning: This build of Skylab is over 30 days old. It is recommended to update it by running "' . $_SERVER['PHP_SELF'] . ' self-update" to get the latest version.');
            }
        }

        $this->doExecute($input, $output);
        $app = $this->getContainer();
        if (!$input->getOption('hideLogo')) {
            OutputUtil::logStatistics($output, OutputInterface::VERBOSITY_NORMAL, $app['skylab.starttime']);
        }
    }

    abstract protected function doExecute(InputInterface $input, OutputInterface $output);

}
