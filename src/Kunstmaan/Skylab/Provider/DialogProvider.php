<?php
namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use RuntimeException;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputInterface;

/**
 * DialogProvider
 */
class DialogProvider extends AbstractProvider
{

    /**
     * @var Application
     */
    private $app;

    /**
     * @var DialogHelper
     */
    private $dialog;

    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
	$app['dialog'] = $this;
	$this->app = $app;
	$this->dialog = $this->app['console']->getHelperSet()->get('dialog');
    }

    /**
     * @param string $argumentname The argument name
     * @param string $message The message
     * @param InputInterface $input The command input stream
     *
     * @return string
     * @throws \RuntimeException
     */
    public function askFor($argumentname, $message, InputInterface $input)
    {
	$name = $input->getArgument($argumentname);
	if (is_null($name)) {
	    $name = $this->dialog->ask($this->output, '<question>' . $message . ': </question>');
	}
	if (is_null($name)) {
	    throw new RuntimeException("A $argumentname is required, what am I, psychic?");
	}

	return $name;
    }

    /**
     * @param string $question The question text
     * @param bool $default The default action
     */
    public function askConfirmation($question, $default = true)
    {
	$this->dialog->askConfirmation($this->output, $question, $default);
    }
}