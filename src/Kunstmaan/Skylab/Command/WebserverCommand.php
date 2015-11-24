<?php

namespace Kunstmaan\Skylab\Command;

use Symfony\Component\Console\Input\InputArgument;

class WebserverCommand extends AbstractCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->addDefaults()
            ->setName('webserver')
            ->setDescription('Perform webserver operations with abstraction of the webserver engine.')
            ->addArgument('action', InputArgument::REQUIRED, 'Possible actions: reload')
            ->setHelp(<<<EOT
Perform webserver operations with abstraction of the webserver engine.

<info>php skylab.phar webserver reload</info>              # Reload webserver configuration

EOT
            );
    }

    /**
     * @throws \RuntimeException
     */
    protected function doExecute()
    {

        $action = $this->input->getArgument('action');

        if($action === 'reload')
        {

            if ($this->app["config"]["webserver"]["engine"] == 'nginx') {
                $this->processProvider->executeSudoCommand("service nginx reload");
            } else {
                $this->processProvider->executeSudoCommand("service apache2 reload");
            }

        }
        else
        {
            $this->dialogProvider->logError("Webserver action '$action' not implemented", true);
        }

    }
}
