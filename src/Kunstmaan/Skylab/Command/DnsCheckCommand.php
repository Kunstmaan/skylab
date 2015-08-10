<?php

namespace Kunstmaan\Skylab\Command;

use Symfony\Component\Console\Input\InputArgument;

class DnsCheckCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->addDefaults()
            ->setName('dnscheck')
            ->setDescription('Get the current DNS settings for all domains configured for this project')
            ->addArgument('project', InputArgument::OPTIONAL, 'The name of the Skylab project')
            ->setHelp(<<<EOT
The <info>nameservercheck</info> command get the current DNS settings for all domains configured for this project.

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
                $dnsSettings = $this->processProvider->executeCommand('dig +short ' . $domain);
                $results[] = array($domain, trim($dnsSettings)."\n");
            }

            $this->dialogProvider->renderTable(array('Project', 'DNS settings'), $results);
        } else {
            $this->dialogProvider->logError('Project not found');
        }
    }
}
