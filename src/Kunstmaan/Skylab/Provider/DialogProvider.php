<?php

namespace Kunstmaan\Skylab\Provider;

use Kunstmaan\Skylab\Application as Skylab;
use Kunstmaan\Skylab\Exceptions\SkylabException;
use Pimple\Container;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class DialogProvider
 *
 * @package Kunstmaan\Skylab\Provider
 */
class DialogProvider extends AbstractProvider
{
    /**
     * @var QuestionHelper
     */
    private $dialog;

    /**
     * @var ProgressBar
     */
    private $progress;

    /**
     * Registers services on the given app.
     *
     * @param Container $app An Application instance
     */
    public function register(Container $app)
    {
        $app['dialog'] = $this;
        $this->app = $app;
        /** @var Skylab $consoleApp */
        $consoleApp = $this->app['console'];
        $this->dialog = $consoleApp->getHelperSet()->get('question');
    }

    /**
     * @param string       $message
     * @param  string|null $argumentname
     * @param  null        $default
     *
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
                $question = new Question('<question>'.$message.'</question> ');
                $var = $this->dialog->ask($this->input, $this->output, $question);
            }
        } elseif ($default) {
            if ($this->noInteraction) {
                $this->dialogProvider->logNotice("--no-interaction selected, using ".$default);
                $var = $default;
            } else {
                $question = new Question('<question>'.$message.':  ['.$default.']</question> ', $default);
                $var = $this->dialog->ask($this->input, $this->output, $question);
            }
        } else {
            $question = new Question('<question>'.$message.'</question> ');
            $var = $this->dialog->ask($this->input, $this->output, $question);
        }

        return $var;
    }

    /**
     * @param $message
     *
     * @return string
     */
    public function askHiddenResponse($message)
    {
        $this->clearLine();
        $this->output->writeln("\n");
        $question = new Question('<question>'.$message.'</question> ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $var = $this->dialog->ask($this->input, $this->output, $question);

        return $var;
    }

    /**
     * @param  string $question The question text
     * @param  bool   $default  The default action
     *
     * @return bool
     */
    public function askConfirmation($question, $default = true)
    {
        $question = new ConfirmationQuestion($question, $default);

        return $this->dialog->ask($this->input, $this->output, $question);
    }

    /**
     * @param string $message
     */
    public function logStep($message)
    {
        $this->clearLine();
        $this->output->writeln("<fg=green;options=bold>-  ".$message.'</fg=green;options=bold>');
    }

    /**
     * @param string $message
     */
    public function logTask($message)
    {
        $this->clearLine();
        $this->output->writeln('<fg=blue;options=bold>   > '.$message." </fg=blue;options=bold>");
        if ($this->output->getVerbosity() <= OutputInterface::VERBOSITY_NORMAL) {
            $this->progress = new ProgressBar($this->output);
            $this->progress->setEmptyBarCharacter(' ');
            $this->progress->setBarCharacter('-');
            $this->progress->start();
        }
    }

    /**
     * @param string $message
     */
    public function logCommand($message)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->write('<info>   $</info> <comment>'.$message.'</comment> ');
        }
    }

    /**
     * @param string $message
     */
    public function logCommandTime($startTime)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln(' <fg=yellow;options=bold>'.round(microtime(true) - $startTime, 2).'s</fg=yellow;options=bold>');
        } else {
            if ($this->progress != null) {
                $this->progress->advance();
            }
        }
    }

    /**
     * @param string $message
     */
    public function logOutput($message, $error = false, $silent = false)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE || !$silent) {
            if ($error) {
                $this->output->write("<error>".$message.'</error>');
            } else {
                $this->output->write($message);
            }
        } else {
            if ($this->progress != null) {
                $this->progress->advance();
            }
        }
    }

    /**
     * @param  string   $message
     * @param  string[] $extra
     *
     * @return string
     */
    public function logQuery($message, $extra = null)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln(
                '<info>   ~</info> <comment>'.
                $message.
                ($extra ?
                    ' ('.
                    implode(
                        ', ',
                        array_map(
                            function ($v, $k) {
                                return $k.'='.$v;
                            },
                            $extra,
                            array_keys($extra)
                        )
                    ).
                    ')' : '').
                '</comment> '
            );
        } else {
            if ($this->progress != null) {
                $this->progress->advance();
            }
        }

        return $message;
    }

    /**
     * @param string $message
     */
    public function logConfig($message)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln('<info>   %</info> <comment>'.$message.'</comment> ');
        } else {
            if ($this->progress != null) {
                $this->progress->advance();
            }
        }
    }

    /**
     * @param string $message
     */
    public function logNotice($message)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln('<info>   !</info> <comment>'.$message.'</comment> ');
        } else {
            if ($this->progress != null) {
                $this->progress->advance();
            }
        }
    }

    /**
     * @param           $message
     * @param bool|true $report
     *
     * @throws SkylabException
     */
    public function logError($message, $report = true)
    {
        if ($report) {
            throw new SkylabException($message);
        }

        $this->output->writeln("\n\n<error>  ".$message."</error>\n\n");
        exit(1);
    }

    /**
     * @param \Exception $ex
     * @param array      $tags
     * @param array      $extra
     *
     * @throws \Exception
     */
    public function logException(\Exception $ex, $tags = [], $extra = [])
    {
        $ravenClient = new \Raven_Client('https://da7e699379b84d8588b837bd518a2a84:83e238c55e4e42a882b8eaf9ef7f16f3@app.getsentry.com/49959');
        $tags['php_version'] = PHP_VERSION;
        $tags['skylab_version'] = \Kunstmaan\Skylab\Application::VERSION;
        $tags['user'] = posix_getpwuid(posix_geteuid())['name'];
        $extra = array_merge($extra, $this->app["config"]);
        if (\Kunstmaan\Skylab\Application::VERSION !== "@package_version@") {
            $event_id = $ravenClient->getIdent(
                $ravenClient->captureException(
                    $ex,
                    [
                        'extra' => $extra,
                        'tags' => $tags,
                    ]
                )
            );
            $this->output->writeln(
                "\n\n<error>  ".$ex->getMessage(
                )."\n  This exception has been reported with id $event_id. Please log a github issue at https://github.com/Kunstmaan/skylab/issues and mention this id.</error>\n\n"
            );
        }

        echo $ex->getTraceAsString();
        throw $ex;
    }

    /**
     * @param string $message
     */
    public function logWarning($message)
    {
        $this->output->writeln("<fg=black;bg=yellow;options=bold>\n\n".$message."\n</fg=black;bg=yellow;options=bold>\n\n");
    }

    /**
     * @param $rows
     */
    public function renderTable($headers, $rows)
    {
        $table = new Table($this->output);
        $table->setHeaders($headers)->setRows($rows);
        $table->render();
    }

    /**
     * @param OutputInterface $output
     * @param int             $verbosity
     * @param string          $txt
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
     * @param int             $verbosity
     * @param int             $startTime
     */
    public static function logStatistics(OutputInterface $output, $verbosity, $startTime)
    {
        if ($output->getVerbosity() >= $verbosity) {
            $output->writeln(
                "\n<fg=yellow;options=bold>Memory usage: ".round(memory_get_usage() / 1024 / 1024, 2).'MB (peak: '.round(
                    memory_get_peak_usage() / 1024 / 1024,
                    2
                ).'MB), time: '.round(microtime(true) - $startTime, 2)."s</fg=yellow;options=bold>\n"
            );
        }
    }

    /**
     *
     */
    public function clearLine()
    {
        $message = str_pad("", 100, "\x20");
        $this->output->write("\x0D");
        $this->output->write($message);
        $this->output->write("\x0D");
    }
}
