<?php
namespace Kunstmaan\Skylab\Helper;

use Kunstmaan\Skylab\Application;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * OutputUtil
 */
class OutputUtil
{

    public static function logo(OutputInterface $output, $verbosity, $txt)
    {
        if ($output->getVerbosity() >= $verbosity) {
            $output->write(Application::$logo);
            $output->writeln("<fg=yellow;options=bold>$txt</fg=yellow;options=bold>\n");
        }
    }

    /**
     * @param OutputInterface $output The command output stream
     * @param int $verbosity The minimum verbosity level
     * @param string $action The action
     * @param string $txt The actual command
     * @param string $indent
     * @return string
     */
    public static function log(OutputInterface $output, $verbosity, $action, $txt = null, $indent = "")
    {
	if ($output->getVerbosity() >= $verbosity) {
	    if (is_null($txt)) {
		$output->write('<info>' . $indent . '   ></info> ' . $action . " ");
	    } else {
		OutputUtil::newLine($output);
		$output->write('<info>' . $indent . '   ' . $action . '</info> <comment>' . $txt . '</comment> ');
	    }
	} else {
	    $output->write("<fg=blue;options=bold>.</fg=blue;options=bold>");
	}
	return $txt;
    }

    /**
     * @param OutputInterface $output
     */
    public static function newLine(OutputInterface $output){
	$output->writeln('');
    }

    /**
     * @param OutputInterface $output The command output stream
     * @param int $verbosity The minimum verbosity level
     * @param string $action The action
     *
     * @return string
     */
    public static function logStep(OutputInterface $output, $verbosity, $action)
    {
	if ($output->getVerbosity() >= $verbosity) {
	    $output->writeln('<fg=green;options=bold>-  ' . $action . '</fg=green;options=bold>');
	}
    }

    /**
     * @param OutputInterface $output The command output stream
     * @param int $verbosity The minimum verbosity level
     * @param string $msg The error message
     */
    public static function logError(OutputInterface $output, $verbosity, $msg)
    {
        if ($output->getVerbosity() >= $verbosity) {
            $output->writeln("<error>  " . $msg . "</error>");
        }
    }

    /**
     * @param OutputInterface $output
     * @param $verbosity
     * @param $startTime
     */
    public static function logStatistics(OutputInterface $output, $verbosity, $startTime)
    {
	if ($output->getVerbosity() >= $verbosity) {
	    $output->writeln("\n<fg=yellow;options=bold>Memory usage: " . round(memory_get_usage() / 1024 / 1024, 2) . 'MB (peak: ' . round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB), time: ' . round(microtime(true) - $startTime, 2) . "s</fg=yellow;options=bold>\n");
	}
    }
}