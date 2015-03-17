<?php

namespace Kunstmaan\Skylab\Command;

use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
            ->setDescription('Get an easy ')
            ->setHelp(<<<EOT
The <info>fetch</info> command fetches a Skylab project from a server and puts it in the right locations on your computer. It
will also drop the databases, so be very careful if you want to use this on a production server to do a migration.

<info>php skylab.phar fetch</info>                         # Will ask for a project and server to fetch it from
<info>php skylab.phar fetch testproject server1</info>     # Will fetch the testproject from server1

EOT
            );
    }

    /**
     * @throws \RuntimeException
     */
    protected function doExecute()
    {
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
                preg_match_all('/inet addr: ?([^ ]+)/', `ifconfig`, $ips);
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
