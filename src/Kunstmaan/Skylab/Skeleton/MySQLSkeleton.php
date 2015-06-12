<?php
namespace Kunstmaan\Skylab\Skeleton;

use Symfony\Component\Finder\Finder;

/**
 * MySQLSkeleton
 */
class MySQLSkeleton extends AbstractSkeleton
{

    const NAME = "mysql";

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function create(\ArrayObject $project)
    {
        // Check if project name has more than 16 characters as it is invalid as mySql username
        // Side note: Mysql database names have a max lenght of 64 characters
        $user = strlen($project["name"]) > 16 ? null : $project["name"];

        $project["mysqluser"] = $this->dialogProvider->askFor("Enter a MySQL username (max 16 characters)", null, $user);
        $pwgen = new \PWGen();
        $project["mysqlpass"] = $this->dialogProvider->askFor("Enter a MySQL password", null, $pwgen->generate());
        $project["mysqldbname"] = $this->dialogProvider->askFor("Enter a MySQL databasename", null, $project["name"]);
        $project["mysqlserver"] = $this->dialogProvider->askFor("Enter a MySQL server host", null, "localhost");
    }

    /**
     * @return mixed
     */
    public function preMaintenance()
    {
    }

    /**
     * @return mixed
     */
    public function postMaintenance()
    {
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function maintenance(\ArrayObject $project)
    {
        if (!isset($project["mysqlserver"]) || !isset($project["mysqldbname"]) || !isset($project["mysqluser"]) || !isset($project["mysqlpass"])){
            $this->dialogProvider->logNotice("Required MySQL configuration is missing");
            return;
        }
        try {
            new \PDO('mysql:host=' . $project["mysqlserver"] . ';dbname=' . $project["mysqldbname"], $project["mysqluser"], $project["mysqlpass"]);
        } catch (\PDOException $exLoginTest) {
            $this->dialogProvider->logNotice("Cannot connect as " . $project["mysqluser"] . ", lets test if the database exists (" . $exLoginTest->getMessage() . ")");
            try {
                new \PDO('mysql:host=' . $project["mysqlserver"] . ';dbname=' . $project["mysqldbname"], $this->app["config"]["mysql"]["user"], $this->app["config"]["mysql"]["password"]);
                $this->dialogProvider->logNotice("Database " . $project["mysqldbname"] . " exists!");
            } catch (\PDOException $exDBTest) {
                $this->dialogProvider->logNotice("Cannot connect to the " . $project["mysqldbname"] . " database as ".$this->app["config"]["mysql"]["user"]." as well, lets create it. (" . $exDBTest->getMessage() . ")");
                $backupDir = $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/backup/";
                $pdo = new \PDO('mysql:host=' . $project["mysqlserver"] . ";", $this->app["config"]["mysql"]["user"], $this->app["config"]["mysql"]["password"]);
                $pdo->exec($this->dialogProvider->logQuery("create database " . $project["mysqldbname"] . " DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci"));
                $finder = new Finder();
                $finder->files()->in($backupDir)->name("mysql.dmp.gz");
                if (count(iterator_to_array($finder)) > 0) {
                    $this->processProvider->executeCommand('gzip -dc ' . $backupDir . '/mysql.dmp.gz | mysql -h ' . $project["mysqlserver"] . ' -u root -p' . $this->app["config"]["mysql"]["password"] . ' ' . $project["mysqldbname"]);
                }

            }
            $pdo = new \PDO('mysql:host=' . $project["mysqlserver"] . ";", $this->app["config"]["mysql"]["user"], $this->app["config"]["mysql"]["password"]);
            $pdo->exec($this->dialogProvider->logQuery("GRANT ALL PRIVILEGES ON " . $project["mysqldbname"] . ".* TO " . $project["mysqluser"] . "@localhost IDENTIFIED BY '" . $project["mysqlpass"] . "'"));
            $pdo->exec($this->dialogProvider->logQuery("GRANT ALL PRIVILEGES ON " . $project["mysqldbname"] . ".* TO " . $project["mysqluser"] . "@'%%' IDENTIFIED BY '" . $project["mysqlpass"] . "'"));
        }
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function preBackup(\ArrayObject $project)
    {
        $backupDir = $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/backup/";
        $this->fileSystemProvider->createDirectory($project, "backup");
        $this->processProvider->executeSudoCommand('rm -f ' . $backupDir . '/mysql.dmp');
        if (is_file($backupDir . '/mysql.dmp.gz')) {
            $this->processProvider->executeSudoCommand('rm -f ' . $backupDir . '/mysql.dmp.previous.gz');
            $this->processProvider->executeSudoCommand('mv ' . $backupDir . '/mysql.dmp.gz ' . $backupDir . '/mysql.dmp.previous.gz');
        }
        $this->processProvider->executeSudoCommand("echo 'SET autocommit=0;' > " . $backupDir . "/mysql.dmp");
        $this->processProvider->executeSudoCommand("echo 'SET unique_checks=0;' >> " . $backupDir . "/mysql.dmp");
        $this->processProvider->executeSudoCommand("echo 'SET foreign_key_checks=0;' >> " . $backupDir . "/mysql.dmp");
        $this->processProvider->executeSudoCommand("mysqldump --skip-opt --add-drop-table --add-locks --create-options --disable-keys --single-transaction --skip-extended-insert --quick --set-charset -u " . $project["mysqluser"] . " -p" . $project["mysqlpass"] . " " . $project["mysqldbname"] . " >> " . $backupDir . "/mysql.dmp");
        $this->processProvider->executeSudoCommand("echo 'COMMIT;' >> " . $backupDir . "/mysql.dmp");
        $this->processProvider->executeSudoCommand("echo 'SET autocommit=1;' >> " . $backupDir . "/mysql.dmp");
        $this->processProvider->executeSudoCommand("echo 'SET unique_checks=1;' >> " . $backupDir . "/mysql.dmp");
        $this->processProvider->executeSudoCommand("echo 'SET foreign_key_checks=1;' >> " . $backupDir . "/mysql.dmp");
        $this->processProvider->executeSudoCommand("gzip " . $backupDir . "/mysql.dmp -f");
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function postBackup(\ArrayObject $project)
    {
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function preRemove(\ArrayObject $project)
    {
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function postRemove(\ArrayObject $project)
    {
        $pdo = new \PDO('mysql:host=' . $project["mysqlserver"] . ";", "root", $this->app["config"]["mysql"]["password"]);
        $pdo->exec($this->dialogProvider->logQuery("drop database if exists " . $project["mysqldbname"]));
    }

    /**
     * @param  \ArrayObject      $project
     * @param  \SimpleXMLElement $config
     * @return \SimpleXMLElement
     */
    public function writeConfig(\ArrayObject $project, \SimpleXMLElement $config)
    {
        $config = $this->projectConfigProvider->addVar($config, 'project.mysqluser', $project["mysqluser"]);
        $config = $this->projectConfigProvider->addVar($config, 'project.mysqlpass', $project["mysqlpass"]);
        $config = $this->projectConfigProvider->addVar($config, 'project.mysqldbname', $project["mysqldbname"]);
        $config = $this->projectConfigProvider->addVar($config, 'project.mysqlserver', $project["mysqlserver"]);

        return $config;
    }

    /**
     * @return string[]
     */
    public function dependsOn()
    {
        return array("base");
    }

}
