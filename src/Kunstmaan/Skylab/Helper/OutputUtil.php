<?php
namespace Kunstmaan\Skylab\Helper;

use Symfony\Component\Console\Output\OutputInterface;
use Kunstmaan\Skylab\Application;

/**
 * OutputUtil
 */
class OutputUtil
{

    public static function logo(OutputInterface $output, $verbosity, $txt){
        if ($output->getVerbosity() >= $verbosity) {
            $output->write(Application::$logo);
            $output->writeln("<fg=yellow;options=bold>$txt</fg=yellow;options=bold>\n");
        }
    }

    /**
     * @param OutputInterface $output    The command output stream
     * @param int             $verbosity The minimum verbosity level
     * @param string          $action    The action
     * @param string          $txt       The actual command
     *
     * @return string
     */
    public static function log(OutputInterface $output, $verbosity, $action, $txt = null)
    {
        if ($output->getVerbosity() >= $verbosity) {
            if (is_null($txt)) {
                $output->writeln('<info>  ></info> ' . $action);
            } else {
                $output->writeln('<info>  ' . $action . '</info> <comment>' . $txt . '</comment>');
            }
        }

        return $txt;
    }

    /**
     * @param OutputInterface $output    The command output stream
     * @param int             $verbosity The minimum verbosity level
     * @param string          $msg       The error message
     */
    public static function logError(OutputInterface $output, $verbosity, $msg)
    {
        if ($output->getVerbosity() >= $verbosity) {
            $output->writeln("<error>  " . $msg . "</error>");
        }
    }
}