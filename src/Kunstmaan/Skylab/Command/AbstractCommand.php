<?php
namespace Kunstmaan\Skylab\Command;

use Cilex\Command\Command;
use Kunstmaan\Skylab\Provider\UsesProviders;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{

    use UsesProviders;

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setup($this->getContainer(), $input, $output, true);
        $this->doPreExecute();
        $this->doExecute();
        $this->doPostExecute();
    }

    abstract protected function doExecute();

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

    /**
     * @return $this
     */
    public function addDefaults()
    {
        $this
            ->addOption("--hideLogo", null, InputOption::VALUE_NONE, 'If set, no logo or statistics will be shown')
            ->addOption("--no-interactive", null, InputOption::VALUE_NONE, 'If set, no questions will be asked');

        return $this;
    }

}
