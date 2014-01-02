<?php

namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Symfony\Component\Process\Process;

/**
 * ProcessProvider
 */
class ProcessProvider extends AbstractProvider
{
    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['process'] = $this;
        $this->app = $app;
    }

    /**
     * @param string $command The command
     * @param bool   $silent  Be silent or not
     *
     * @param  callable    $callback
     * @return bool|string
     */
    public function executeCommand($command, $silent = false, \Closure $callback = null)
    {
        if (!$silent) {
            $this->dialogProvider->logCommand($command);
        }
        $process = new Process($command);
        $process->setTimeout(3600);
        $process->run($callback);
        if (!$process->isSuccessful()) {
            if (!$silent) {
                $this->dialogProvider->logError($process->getErrorOutput());
            }

            return false;
        }

        return $process->getOutput();
    }

    /**
     * @param string $command The command
     * @param bool   $silent  Be silent or not
     * @param string $sudoAs  Sudo as a different user then the root user
     *
     * @return bool|string
     */
    public function executeSudoCommand($command, $silent = false, $sudoAs = null)
    {
        if (empty($sudoAs)) {
            $command = 'sudo -p "Please enter your sudo password:" ' . $command;
        } else {
            $command = 'sudo -p "Please enter your sudo password:" -u ' . $sudoAs . ' ' . $command;
        }

        return $this->executeCommand($command, $silent);
    }

    /**
     * @param  string $cmd
     * @return bool
     */
    public function commandExists($cmd)
    {
        return shell_exec("hash " . $cmd . " 2>&1") == '';
    }
}
