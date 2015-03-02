<?php
namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Kunstmaan\Skylab\Application as Skylab;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * DialogProvider
 */
class DialogProvider extends AbstractProvider
{

    /**
     * @var DialogHelper
     */
    private $dialog;

    /**
     * @var ProgressHelper
     */
    private $progress;

    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['dialog'] = $this;
        $this->app = $app;
        /** @var Skylab $consoleApp */
        $consoleApp = $this->app['console'];
        $this->dialog = $consoleApp->getHelperSet()->get('dialog');
        $this->progress = $consoleApp->getHelperSet()->get('progress');
        $this->progress->setEmptyBarCharacter(' ');
        $this->progress->setBarCharacter('-');
    }

    /**
     * @param string $message
     * @param  string|null              $argumentname
     * @param  null              $default
     * @return string
     * @throws \RuntimeException
     */
    public function askFor($message, $argumentname = null, $default = null)
    {
        $this->clearLine();
        $this->output->writeln("\n");
        if ($argumentname) {
            $var = $this->input->getArgument($argumentname);
            if (!$var) {
                $var = $this->dialog->ask($this->output, '<question>' . $message . '</question> ');
            }
        } elseif ($default) {
            if ($this->noInteraction) {
                $this->dialogProvider->logNotice("--no-iteraction selected, using " . $default);
                $var = $default;
            } else {
                $var = $this->dialog->ask($this->output, '<question>' . $message . ':  [' . $default . ']</question> ', $default);
            }
        } else {
            $var = $this->dialog->ask($this->output, '<question>' . $message . '</question> ');
        }

        return $var;
    }

    /**
     * @param string $message
     * @param  string|null              $argumentname
     * @param  null              $default
     * @return string
     * @throws \RuntimeException
     */
    public function askHiddenResponse($message)
    {
        $this->clearLine();
        $this->output->writeln("\n");
        $var = $this->dialog->askHiddenResponse(
          $this->output,
          '<question>' . $message . '</question> ',
          false
        );

        return $var;
    }

    /**
     * @param  string $question The question text
     * @param  bool   $default  The default action
     * @return bool
     */
    public function askConfirmation($question, $default = true)
    {
        return $this->dialog->askConfirmation($this->output, $question, $default);
    }

    /**
     * @param string $message
     */
    public function logStep($message)
    {
        $this->clearLine();
        $this->output->writeln("<fg=green;options=bold>-  " . $message . '</fg=green;options=bold>');
    }

    /**
     * @param string $message
     */
    public function logTask($message)
    {
        $this->clearLine();
        $this->output->writeln('<fg=blue;options=bold>   > ' . $message . " </fg=blue;options=bold>");
        if ($this->output->getVerbosity() <= OutputInterface::VERBOSITY_NORMAL) {
            $this->progress->start($this->output);
        }
    }

    /**
     * @param string $message
     */
    public function logCommand($message)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln('<info>   $</info> <comment>' . $message . '</comment> ');
        } else {
            $this->progress->advance();
        }
    }

    /**
     * @param  string   $message
     * @param  string[] $extra
     * @return string
     */
    public function logQuery($message, $extra=null)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln('<info>   ~</info> <comment>' .
                $message .
                ($extra ?
                    ' (' .
                        implode(', ',
                            array_map(function ($v, $k) { return $k . '=' . $v; }, $extra, array_keys($extra))
                        ) .
                    ')' : '') .
            '</comment> ');
        } else {
            $this->progress->advance();
        }

        return $message;
    }

    /**
     * @param string $message
     */
    public function logConfig($message)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln('<info>   %</info> <comment>' . $message . '</comment> ');
        } else {
            $this->progress->advance();
        }
    }

    /**
     * @param string $message
     */
    public function logNotice($message)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln('<info>   !</info> <comment>' . $message . '</comment> ');
        } else {
            $this->progress->advance();
        }
    }

    /**
     * @param string $message
     */
    public function logError($message)
    {
        $this->output->writeln("\n\n<error>  " . $message . "</error>\n\n");
    }

    /**
     * @param string $message
     */
    public function logWarning($message)
    {
        $this->output->writeln("<fg=black;bg=yellow;options=bold>\n\n" . $message . "\n</fg=black;bg=yellow;options=bold>\n\n");
    }

    /**
     * @param string $txt
     */
    public static function logo(OutputInterface $output, $verbosity, $txt)
    {
        if ($output->getVerbosity() >= $verbosity) {
            $output->write(Skylab::$logo);
            $output->writeln("<fg=yellow;options=bold>$txt</fg=yellow;options=bold>\n");
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

    /**
     *
     */
    public function clearLine()
    {
        $message = str_pad("", 100, "\x20", STR_PAD_RIGHT);
        $this->output->write("\x0D");
        $this->output->write($message);
        $this->output->write("\x0D");
    }
}
