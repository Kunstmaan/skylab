<?php

namespace Kunstmaan\Skylab\Command;


class ShareCommand extends AbstractCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->addDefaults()
            ->setName('share')
            ->setDescription('Get a full table of all your projects with the xip.io url')
            ->setHelp(<<<EOT
The <info>share</info> command show a table of all your locally installed projects together with the xip.io url.

<info>php skylab.phar share</info>                         # Will show the xip.io table

EOT
            );
    }

    /**
     * @throws \RuntimeException
     */
    protected function doExecute()
    {
        if(!$this->app["config"]["develmode"]) {
           $this->dialogProvider->logWarning('develmode=false, You do not have xip.io urls when your develmode is set to false');
           return;
        }
        $rows = array();
        $ip = $this->getIP();
        $projects = $this->fileSystemProvider->getProjects();
        foreach($projects as $key => $projectFile) {
            $projectname = $projectFile->getFilename();
            $rows[] = array($projectname, 'http://'.$projectname.'.'.$ip.'.xip.io');
        }
        $this->dialogProvider->renderTable(array('Project', 'URL'), $rows);
    }

    private function getIP() {
        $os = strtolower(PHP_OS);
        switch ($os) {
            case 'linux': //Linux
                preg_match_all('/inet addr: ?([^ ]+)/', `ifconfig |grep "inet " |grep -v "127.0.0.1"`, $ips);
                break;
            case 'darwin': //OSX
                preg_match_all('/inet ?([^ ]+)/', `ifconfig -au |grep "inet " |grep -v "127.0.0.1"`, $ips);
                break;
            default:
                throw new \Exception("Unsupported OS: " . $os);
                break;
        }
        return $ips[1][0];
    }
}
