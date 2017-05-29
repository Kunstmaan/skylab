<?php

namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

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
     * @param  \Closure    $callback
     * @return bool|string
     */
    public function executeCommand($command, $silent = false, \Closure $callback = null, $env=array())
    {
        return $this->performCommand($command, $silent, $callback, $env);
    }

    /**
     * @param string $command The command
     * @param bool   $silent  Be silent or not
     * @param string $sudoAs  Sudo as a different user then the root user
     *
     * @return bool|string
     */
    public function executeSudoCommand($command, $silent = false, $sudoAs = null, \Closure $callback = null, $env=array())
    {
        if (empty($sudoAs)) {
            $command = 'sudo -s -p "Please enter your sudo password:" ' . '\'' . $command . '\'';
        } else {
            $command = 'sudo -s -p "Please enter your sudo password:" -u ' . $sudoAs . ' ' . $command;
        }
        return $this->performCommand($command, $silent, $callback, $env);
    }

    /**
     * @param  string $cmd
     * @return bool
     */
    public function commandExists($cmd)
    {
        return shell_exec("hash " . $cmd . " 2>&1") == '';
    }

    /**
     * @param $command
     * @param $silent
     * @param \Closure $callback
     * @param $env
     * @return bool|string
     * @throws \Kunstmaan\Skylab\Exceptions\SkylabException
     */
    private function performCommand($command, $silent=false, \Closure $callback = null, $env=array())
    {
        $startTime = microtime(true);

        if (!$silent) {
            $this->dialogProvider->logCommand($command);
        }

        $env = array_replace($_ENV, $_SERVER, $env);
        $process = new Process($command, null, $env);
        $process->setTimeout(14400*100);
        $process->run($callback);
        if (!$silent) {
            $this->dialogProvider->logCommandTime($startTime);
        }
        if (!$process->isSuccessful()) {
            if ($process->getExitCode() == 23){
                return $process->getOutput();
            } else {
                if (!$silent) {
                    $this->dialogProvider->logError($process->getErrorOutput());
                }
                return false;
            }
        }
        return $process->getOutput();
    }
}
