<?php

namespace Kunstmaan\Skylab\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FetchCommand extends AbstractCommand
{

    const TYPE_JAVA = "java";
    const TYPE_PHP = "php";

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->addDefaults()
            ->setName('fetch')
            ->setDescription('Fetches a project from a production server')
            ->addArgument('project', InputArgument::OPTIONAL, 'The name of the Skylab project')
            ->addArgument('host', InputArgument::OPTIONAL, 'The hostname of the server to fetch from')
            ->addOption('location', 'l', InputOption::VALUE_OPTIONAL, 'Override the target location')
            ->addOption('no-database', null, InputOption::VALUE_NONE, 'Don\'t delete the local database')
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

        $location = $this->input->getOption("location");
        if ($location){
            $config = $this->app["config"];
            $projects = $config["projects"];
            $projects["path"] = $location;
            $config["projects"] = $projects;
            $this->app["config"]= $config;
        }

        $projectname = $this->dialogProvider->askFor("Please enter the name of the project", 'project');
        $hostname = $this->dialogProvider->askFor("Please enter the hostname of the server", 'host');

        $this->dialogProvider->logStep("Checking preconditions");
        $this->dialogProvider->logTask("Checking the server");
        $exists = $this->isRemoteProjectAvailable($projectname, $hostname);
        if (!$exists) {
            $this->dialogProvider->logError("The project " . $projectname . " does not exist on " . $hostname, false);
        }
        $this->dialogProvider->logTask("Detecting the project type");
        $type = $this->detectProjectType($projectname, $hostname);

        $excludes = array(
            ".composer",
            "apachelogs/*",
            "resizedcache/*",
            "nobackup/*",
            "tmp/*",
            ".viminfo",
            ".ssh",
            ".bash_history",
            ".config",
            ".cache",
            ".mysql_history",
            "data/current/app/logs/*",
            "data/current/app/cache/*"
        );

        if (!$this->fileSystemProvider->projectExists($projectname)) {
            $this->dialogProvider->logStep("Running the full rsync commands since " . $projectname . " is not on this computer");
            $fullExcludes = $excludes;
            $fullExcludes[] = "data/shared";
            $fullExcludes[] = "data/builds";
            $fullExcludes[] = "data/releases";
            if ($type !== self::TYPE_JAVA) {
                $fullExcludes[] = "data/" . $projectname;
            }
            $this->fetchFolder(
                $this->app["config"]["projects"]["path"] . '/',
                $hostname,
                $this->app["config"]["remote_projects"]["path"]. "/" . $projectname,
                $fullExcludes,
                true
            );
            if ($type !== self::TYPE_JAVA) {
                $mvCommand = "mv " . $this->fileSystemProvider->getProjectDirectory($projectname) . "/data/current " . $this->fileSystemProvider->getProjectDirectory($projectname) . "/data/" . $projectname;
                $this->processProvider->executeCommand($mvCommand);
            }
        } else {
            $this->dialogProvider->logStep("Running the update rsync commands since " . $projectname . " already is on this computer");
            $updateExcludes = $excludes;
            $updateExcludes[] = "data/*";

            $this->fetchFolderIfExists(
                $this->fileSystemProvider->getProjectDirectory($projectname). '/',
                $hostname,
                "/home/projects/" . $projectname . '/*',
                $updateExcludes
            );

            $this->fetchFolderIfExists(
                $this->fileSystemProvider->getProjectDirectory($projectname) . "/data/" . $projectname . "/web/uploads/",
                $hostname,
                "/home/projects/" . $projectname . "/data/current/web/uploads/*",
                $updateExcludes
            );

            $this->fetchFolderIfExists(
                $this->fileSystemProvider->getProjectDirectory($projectname) . "/data/" . $projectname . "/sites/default/files/",
                $hostname,
                "/home/projects/" . $projectname . "/data/current/sites/default/files/*",
                $updateExcludes
            );
        }
        if (file_exists($this->fileSystemProvider->getProjectDirectory($projectname) . "/data/" . $projectname . '/.skylab/conf') && is_link($this->fileSystemProvider->getProjectDirectory($projectname) . '/conf')) {
        	$this->processProvider->executeSudoCommand("ln -sf " . $this->fileSystemProvider->getProjectDirectory($projectname) . "/data/" . $projectname . '/.skylab/conf ' . $this->fileSystemProvider->getProjectDirectory($projectname).'/conf');
        }
        if (!$this->input->getOption('no-database')) {
            $this->dialogProvider->logStep("Dropping the databases");
            if (in_array("mysql", \PDO::getAvailableDrivers(), TRUE)) {
                $this->dialogProvider->logTask("Dropping the MySQL database");
                $dbh = new \PDO('mysql:host=localhost;', $this->app["config"]["mysql"]["user"], $this->app["config"]["mysql"]["password"]);
                $dbh->query("DROP DATABASE IF EXISTS " . $projectname);
            }
            if (in_array("postgresql", \PDO::getAvailableDrivers(), TRUE)) {
                $this->dialogProvider->logTask("Dropping the PostgreSQL database");
                $dbh = new \PDO(
                    $this->dialogProvider->logQuery(
                        'pgsql:host=localhost;dbname=template1',
                        array(
                            "user" => $this->app["config"]["postgresql"]["user"],
                            "password" => $this->app["config"]["postgresql"]["password"]
                        )
                    ),
                    $this->app["config"]["postgresql"]["user"],
                    $this->app["config"]["postgresql"]["password"]
                );
                $dbh->query("DROP DATABASE IF EXISTS " . $projectname);
            }
        }
    }

    /**
     * Tries to detect if the remote project is a Java project.
     *
     * @param string $projectname
     * @param string $hostname
     * @return string
     */
    private function detectProjectType($projectname, $hostname)
    {
        $command = "ssh " . $hostname . " 'test -d /home/projects/" . $projectname . "/data/" . $projectname . "/src/be/smartlounge && echo found'";
        $this->dialogProvider->logCommand($command);
        $found = $this->processProvider->executeCommand($command, true);
        if ($found) {
            return self::TYPE_JAVA;
        }

        return self::TYPE_PHP;
    }

    /**
     * Tries to detect if the remote project exists
     *
     * @param string $projectname
     * @param string $hostname
     * @return bool
     */
    private function isRemoteProjectAvailable($projectname, $hostname)
    {
        $command = "ssh " . $hostname . " 'test -d /home/projects/" . $projectname . " && echo found'";
        $this->dialogProvider->logCommand($command);
        $found = $this->processProvider->executeCommand($command, true);
        return (bool) $found;
    }

    /**
     * @param string   $folder
     * @param string   $hostname
     * @param string   $remoteFolder
     * @param string[] $excludes
     * @param bool     $links
     */
    private function fetchFolderIfExists($folder, $hostname, $remoteFolder, $excludes, $links = false)
    {
        if (file_exists($folder)) {
            $this->fetchFolder($folder, $hostname, $remoteFolder, $excludes, $links);
        }
    }

    /**
     * @param string   $folder
     * @param string   $hostname
     * @param string   $remoteFolder
     * @param string[] $excludes
     * @param bool     $links
     */
    private function fetchFolder($folder, $hostname, $remoteFolder, $excludes, $links = false)
    {
        $rsyncCommand = "rsync --no-acls -r" . ($links ? "L" : "l") . "Dhz --info=progress2 --delete --size-only";
        foreach ($excludes as $exclude) {
            $rsyncCommand .= " --exclude=" . $exclude;
        }
        $rsyncCommand .= " " . $hostname . ":" . $remoteFolder;
        $rsyncCommand .= " " . $folder;
        $this->processProvider->executeCommand($rsyncCommand, false, function ($type, $buffer) {
            strlen($type); // just to get rid of the scrutinizer error... sigh
            echo $buffer;
        });
    }
}
