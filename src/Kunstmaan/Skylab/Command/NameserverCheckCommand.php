<?php

namespace Kunstmaan\Skylab\Command;

use Symfony\Component\Console\Input\InputArgument;

class NameserverCheckCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->addDefaults()
            ->setName('nameservercheck')
            ->setDescription('Get the current authoritative nameservers for all domains configured for this project')
            ->addArgument('project', InputArgument::OPTIONAL, 'The name of the Skylab project')
            ->setHelp(<<<EOT
The <info>nameservercheck</info> command get the current authoritative nameservers for all domains configured for this project.

<info>php skylab.phar nameservercheck testproject</info>
EOT
            );
    }

    /**
     * @throws \RuntimeException
     */
    protected function doExecute()
    {
        $projectname = $this->dialogProvider->askFor("Please enter the name of the project", 'project');
        if ($this->fileSystemProvider->projectExists($projectname)) {
            $project = $this->projectConfigProvider->loadProjectConfig($projectname);
            $domains = array_merge(array($project['url']), $project['aliases']);
            $results = array();
            foreach ($domains as $domain) {
                $split = array_reverse(explode('.', $domain));
                $rootDomain = $split[1] . '.' . $split[0];
                $nameservers = $this->processProvider->executeCommand('dig +short NS ' . $rootDomain);
                $results[] = array($domain, trim($nameservers)."\n");
            }

            $this->dialogProvider->renderTable(array('Project', 'Nameservers'), $results);
        } else {
            $this->dialogProvider->logError('Project not found');
        }
    }
}
