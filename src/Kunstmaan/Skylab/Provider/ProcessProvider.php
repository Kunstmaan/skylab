<?php

namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;

use Cilex\ServiceProviderInterface;
use Kunstmaan\Skylab\Helper\OutputUtil;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * ProcessProvider
 */
class ProcessProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
	$app['process'] = $this;
    }

    /**
     * @param string $command The command
     * @param OutputInterface $output The command output stream
     * @param bool $silent Be silent or not
     *
     * @return bool|string
     */
    public function executeCommand($command, OutputInterface $output, $silent = false)
    {
	OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "$", $command);
	$process = new Process($command);
	$process->setTimeout(3600);
	$process->run();
	if (!$process->isSuccessful()) {
	    if (!$silent) {
		OutputUtil::logError($output, OutputInterface::VERBOSITY_NORMAL, $process->getErrorOutput());
	    }

	    return false;
	}

	return $process->getOutput();
    }

    /**
     * @param string $command The command
     * @param OutputInterface $output The command output stream
     * @param bool $silent Be silent or not
     * @param string $sudoAs Sudo as a different user then the root user
     *
     * @return bool|string
     */
    public function executeSudoCommand($command, OutputInterface $output, $silent = false, $sudoAs = null)
    {
	if (empty($sudoAs)) {
	    $command = 'sudo ' . $command;
	} else {
	    $command = 'sudo -u ' . $sudoAs . ' ' . $command;
	}

	return $this->executeCommand($command, $output, $silent);
    }
}