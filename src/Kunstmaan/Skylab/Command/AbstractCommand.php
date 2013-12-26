<?php
namespace Kunstmaan\Skylab\Command;

use Cilex\Application;
use Cilex\Command\Command;
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
    protected $fileSystemProvider;

    /**
     * @var ProjectConfigProvider
     */
    protected $projectConfigProvider;

    /**
     * @var SkeletonProvider
     */
    protected $skeletonProvider;

    /**
     * @var ProcessProvider
     */
    protected $processProvider;

    /**
     * @var PermissionsProvider
     */
    protected $permissionsProvider;

    /**
     * @var DialogProvider
     */
    protected $dialogProvider;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUpClassVars($input, $output);
        $this->doPreExecute();
        $this->doExecute();
        $this->doPostExecute();
    }

    abstract protected function doExecute();

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function setUpClassVars(InputInterface $input, OutputInterface $output)
    {
        $providers = array(
            'filesystem' => 'fileSystemProvider',
            'projectconfig' => 'projectConfigProvider',
            'skeleton' => 'skeletonProvider',
            'process' => 'processProvider',
            'permission' => 'permissionsProvider',
            'dialog' => 'dialogProvider',
        );
        foreach ($providers as $service => $variable) {
            $this->$variable = $this->getService($service);
            $this->$variable->setUpClassVars($output, $input);
        }

        $this->output = $output;
        $this->input = $input;
        $this->app = $this->getContainer();
    }

    /**
     *
     */
    private function doPreExecute()
    {
        if (!$this->input->getOption('hideLogo')) {
            $this->dialogProvider->logo($this->output, OutputInterface::VERBOSITY_NORMAL, "Executing " . get_class($this));
        }

        $this->processProvider->executeCommand('sudo -p "Please enter your sudo password: " -v', true);

        if (defined('SKYLAB_DEV_WARNING_TIME') && $this->getName() !== 'self-update') {
            if (time() > SKYLAB_DEV_WARNING_TIME) {
                $this->dialogProvider->logWarning($this->output, OutputInterface::VERBOSITY_NORMAL, 'Warning: This build of Skylab is over 30 days old. It is recommended to update it by running "' . $_SERVER['PHP_SELF'] . ' self-update" to get the latest version.');
            }
        }
    }

    /**
     *
     */
    protected function doPostExecute()
    {
        $this->dialogProvider->clearLine();

        if (!$this->input->getOption('hideLogo')) {
            $this->dialogProvider->logStatistics($this->output, OutputInterface::VERBOSITY_NORMAL, $this->app['skylab.starttime']);
        }
    }


}
