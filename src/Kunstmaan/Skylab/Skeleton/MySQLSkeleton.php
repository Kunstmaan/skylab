<?php

namespace Kunstmaan\Skylab\Skeleton;

use Inet\Neuralyzer\Anonymizer\DB;
use Inet\Neuralyzer\Configuration\Reader;
use Symfony\Component\Finder\Finder;

/**
 * MySQLSkeleton
 */
class MySQLSkeleton extends AbstractSkeleton
{
    /** @var Reader */
    private $reader;

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
     * @return mixed|void
     */
    public function create(\ArrayObject $project)
    {
        $project['mysqluser'] = $this->dialogProvider->askFor('Enter a MySQL username', null, $project['name']);
        $pwgen = new \PWGen();
        $project['mysqlpass'] = $this->dialogProvider->askFor('Enter a MySQL password', null, $pwgen->generate());
        $project['mysqldbname'] = $this->dialogProvider->askFor('Enter a MySQL databasename', null, $project['name']);
        $project['mysqlserver'] = $this->dialogProvider->askFor('Enter a MySQL server host', null, 'localhost');
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
        if (!isset($project["mysqlserver"]) || !isset($project["mysqldbname"]) || !isset($project["mysqluser"]) || !isset($project["mysqlpass"])) {
            $this->dialogProvider->logNotice("Required MySQL configuration is missing");

            return;
        }
        try {
            new \PDO('mysql:host='.$project["mysqlserver"].';dbname='.$project["mysqldbname"], $project["mysqluser"], $project["mysqlpass"]);
        } catch (\PDOException $exLoginTest) {
            $this->dialogProvider->logNotice(
                "Cannot connect as ".$project["mysqluser"].", lets test if the database exists (".$exLoginTest->getMessage().")"
            );
            try {
                new \PDO(
                    'mysql:host='.$project["mysqlserver"].';dbname='.$project["mysqldbname"],
                    $this->app["config"]["mysql"]["user"],
                    $this->app["config"]["mysql"]["password"]
                );
                $this->dialogProvider->logNotice("Database ".$project["mysqldbname"]." exists!");
            } catch (\PDOException $exDBTest) {
                $this->dialogProvider->logNotice(
                    "Cannot connect to the ".$project["mysqldbname"]." database as ".$this->app["config"]["mysql"]["user"]." as well, lets create it. (".$exDBTest->getMessage(
                    ).")"
                );
                $backupDir = $this->fileSystemProvider->getProjectDirectory($project["name"])."/backup/";
                $pdo = new \PDO(
                    'mysql:host='.$project["mysqlserver"].";",
                    $this->app["config"]["mysql"]["user"],
                    $this->app["config"]["mysql"]["password"]
                );
                $pdo->exec(
                    $this->dialogProvider->logQuery(
                        "create database ".$project["mysqldbname"]." DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci"
                    )
                );
                $finder = new Finder();
                $finder->files()->in($backupDir)->name("mysql.dmp.gz");
                if (count(iterator_to_array($finder)) > 0) {
                    $this->processProvider->executeCommand(
                        'gzip -dc '.$backupDir.'/mysql.dmp.gz | mysql -h '.$project["mysqlserver"].' -u root -p'.$this->app["config"]["mysql"]["password"].' '.$project["mysqldbname"]
                    );
                }

            }
            $pdo = new \PDO(
                'mysql:host='.$project["mysqlserver"].";",
                $this->app["config"]["mysql"]["user"],
                $this->app["config"]["mysql"]["password"]
            );
            $pdo->exec(
                $this->dialogProvider->logQuery(
                    "GRANT ALL PRIVILEGES ON ".$project["mysqldbname"].".* TO ".$project["mysqluser"]."@localhost IDENTIFIED BY '".$project["mysqlpass"]."'"
                )
            );
            $pdo->exec(
                $this->dialogProvider->logQuery(
                    "GRANT ALL PRIVILEGES ON ".$project["mysqldbname"].".* TO ".$project["mysqluser"]."@'%%' IDENTIFIED BY '".$project["mysqlpass"]."'"
                )
            );
        }
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed|void
     * @throws \Kunstmaan\Skylab\Exceptions\SkylabException
     */
    public function preBackup(\ArrayObject $project)
    {
        $backupDir = $this->fileSystemProvider->getProjectDirectory($project['name']).'/backup/';
        $this->fileSystemProvider->createDirectory($project, 'backup');

        if ($project['anonymize']) {
            $this->createAnonymizedPackage($project, $backupDir);
        } else {
            $this->processProvider->executeSudoCommand('rm -f '.$backupDir.'/mysql.dmp');
            if (is_file($backupDir.'/mysql.dmp.gz')) {
                $this->processProvider->executeSudoCommand('rm -f '.$backupDir.'/mysql.dmp.previous.gz');
                $this->processProvider->executeSudoCommand('mv '.$backupDir.'/mysql.dmp.gz '.$backupDir.'/mysql.dmp.previous.gz');
            }
            $this->processProvider->executeSudoCommand('touch '.$backupDir.'/mysql.dmp');
            $this->processProvider->executeSudoCommand('chmod -R 755 '.$backupDir.'/mysql.dmp');
            $this->fileSystemProvider->writeProtectedFile($backupDir.'/mysql.dmp', "SET autocommit=0;\n", true);
            $this->fileSystemProvider->writeProtectedFile($backupDir.'/mysql.dmp', "SET unique_checks=0;\n", true);
            $this->fileSystemProvider->writeProtectedFile($backupDir.'/mysql.dmp', "SET foreign_key_checks=0;\n", true);

            $tmpfname = tempnam(sys_get_temp_dir(), 'skylab');
            $this->processProvider->executeSudoCommand(
                'mysqldump --skip-opt --add-drop-table --add-locks --create-options --disable-keys --single-transaction --skip-extended-insert --quick --set-charset -u '.$project['mysqluser'].' -p'.$project['mysqlpass'].' '.$project['mysqldbname'].' >> '.$tmpfname
            );

            $this->processProvider->executeSudoCommand('cat '.$tmpfname.' | sudo tee -a '.$backupDir.'/mysql.dmp');
            $this->processProvider->executeSudoCommand('rm -f '.$tmpfname);

            $this->fileSystemProvider->writeProtectedFile($backupDir.'/mysql.dmp', "COMMIT;\n", true);
            $this->fileSystemProvider->writeProtectedFile($backupDir.'/mysql.dmp', "SET autocommit=1;\n", true);
            $this->fileSystemProvider->writeProtectedFile($backupDir.'/mysql.dmp', "SET unique_checks=1;\n", true);
            $this->fileSystemProvider->writeProtectedFile($backupDir.'/mysql.dmp', "SET foreign_key_checks=1;\n", true);
            $this->processProvider->executeSudoCommand('gzip < '.$backupDir.'/mysql.dmp > '.$backupDir.'/mysql.dmp.gz -f');
        }
    }

    /**
     * @param \ArrayObject $project
     * @param              $backupDir
     *
     * @throws \Kunstmaan\Skylab\Exceptions\SkylabException
     */
    private function createAnonymizedPackage(\ArrayObject $project, $backupDir)
    {
        // Anon READER
        $config = $this->fileSystemProvider->getDirectory($project, 'data/current/.skylab/anon.yml');
        if (!file_exists($config)) {
            $this->dialogProvider->logError("There is no anon.yml file in your .skylab directory");
        }

        $this->reader = new Reader($config);

        $this->processProvider->executeSudoCommand('rm -f '.$backupDir.'/mysql_anonymized.dmp');
        if (is_file($backupDir.'/mysql_anonymized.dmp.gz')) {
            $this->processProvider->executeSudoCommand('rm -f '.$backupDir.'/mysql_anonymized.dmp.previous.gz');
            $this->processProvider->executeSudoCommand('mv '.$backupDir.'/mysql_anonymized.dmp.gz '.$backupDir.'/mysql_anonymized.dmp.previous.gz');
        }
        $this->processProvider->executeSudoCommand('touch '.$backupDir.'/mysql_anonymized.dmp');
        $this->processProvider->executeSudoCommand('chmod -R 755 '.$backupDir.'/mysql_anonymized.dmp');
        $this->fileSystemProvider->writeProtectedFile($backupDir.'/mysql_anonymized.dmp', "SET autocommit=0;\n", true);
        $this->fileSystemProvider->writeProtectedFile($backupDir.'/mysql_anonymized.dmp', "SET unique_checks=0;\n", true);
        $this->fileSystemProvider->writeProtectedFile($backupDir.'/mysql_anonymized.dmp', "SET foreign_key_checks=0;\n", true);

        $tmpfname = tempnam(sys_get_temp_dir(), 'skylab');

        $this->createAnonymizedDatabase($project);
        $this->processProvider->executeSudoCommand(
            'mysqldump --skip-opt --add-drop-table --add-locks --create-options --disable-keys --single-transaction --skip-extended-insert --quick --set-charset -u '.$project['mysqluser'].' -p'.$project['mysqlpass'].' '.$project['mysqldbname'].' | mysql -u '.$project['mysqluser'].' -p'.$project['mysqlpass'].' '.$project['mysqldbname_anonymized']
        );
        $this->anonymizeDatabase($project);
        $this->processProvider->executeSudoCommand(
            'mysqldump --skip-opt --add-drop-table --add-locks --create-options --disable-keys --single-transaction --skip-extended-insert --quick --set-charset -u '.$project['mysqluser'].' -p'.$project['mysqlpass'].' '.$project['mysqldbname_anonymized'].' >> '.$tmpfname
        );
        $this->dropAnonymizedDatabase($project);

        $this->processProvider->executeSudoCommand('cat '.$tmpfname.' | sudo tee -a '.$backupDir.'/mysql_anonymized.dmp');
        $this->processProvider->executeSudoCommand('rm -f '.$tmpfname);

        $this->fileSystemProvider->writeProtectedFile($backupDir.'/mysql_anonymized.dmp', "COMMIT;\n", true);
        $this->fileSystemProvider->writeProtectedFile($backupDir.'/mysql_anonymized.dmp', "SET autocommit=1;\n", true);
        $this->fileSystemProvider->writeProtectedFile($backupDir.'/mysql_anonymized.dmp', "SET unique_checks=1;\n", true);
        $this->fileSystemProvider->writeProtectedFile($backupDir.'/mysql_anonymized.dmp', "SET foreign_key_checks=1;\n", true);
        $this->processProvider->executeSudoCommand('gzip '.$backupDir.'/mysql_anonymized.dmp -f');
    }

    /**
     * @param \ArrayObject $project
     */
    private function createAnonymizedDatabase(\ArrayObject $project)
    {
        $this->dialogProvider->logTask('<info>Anonymizing database for project '.$project['name'].'</info>');

        if (!isset($project['mysqlserver'], $project['mysqldbname'], $project['mysqluser'], $project['mysqlpass'])) {
            $this->dialogProvider->logNotice('Required MySQL configuration is missing');

            return;
        }
        $project['mysqldbname_anonymized'] = $project['mysqldbname'].'_anonymized';
        $this->dialogProvider->logNotice(
            'Creating new database to anonymize '.$project['mysqluser']
        );

        try {
            new \PDO(
                sprintf(
                    'mysql:host=%s;dbname=%s;charset=%s;collate=%s;',
                    $project['mysqlserver'],
                    $project['mysqldbname_anonymized'],
                    $this->reader->getCharset(),
                    $this->reader->getCollate()
                ),
                $this->app['config']['mysql']['user'],
                $this->app['config']['mysql']['password']
            );
            $this->dialogProvider->logNotice('Database '.$project['mysqldbname_anonymized'].' exists!');
        } catch (\PDOException $exDBTest) {
            $pdo = new \PDO(
                'mysql:host='.$project['mysqlserver'].';',
                $this->app['config']['mysql']['user'],
                $this->app['config']['mysql']['password']
            );
            $pdo->exec(
                $this->dialogProvider->logQuery(
                    sprintf(
                        'CREATE DATABASE %s DEFAULT CHARACTER SET %s DEFAULT COLLATE %s',
                        $project['mysqldbname_anonymized'],
                        $this->reader->getCharset(),
                        $this->reader->getCollate()
                    )
                )
            );
            $pdo->exec(
                $this->dialogProvider->logQuery(
                    "GRANT ALL PRIVILEGES ON ".$project['mysqldbname_anonymized'].".* TO ".$project["mysqluser"]."@localhost IDENTIFIED BY '".$project["mysqlpass"]."'"
                )
            );
            $pdo->exec(
                $this->dialogProvider->logQuery(
                    "GRANT ALL PRIVILEGES ON ".$project['mysqldbname_anonymized'].".* TO ".$project["mysqluser"]."@'%%' IDENTIFIED BY '".$project["mysqlpass"]."'"
                )
            );
        }
    }

    /**
     * @param \ArrayObject $project
     */
    private function dropAnonymizedDatabase(\ArrayObject $project)
    {
        $pdo = new \PDO(
            sprintf(
                'mysql:host=%s;dbname=%s;charset=%s;collate=%s;',
                $project['mysqlserver'],
                $project['mysqldbname_anonymized'],
                $this->reader->getCharset(),
                $this->reader->getCollate()
            ),
            'root',
            $this->app['config']['mysql']['password']
        );
        $pdo->exec($this->dialogProvider->logQuery('drop database if exists '.$project['mysqldbname_anonymized']));
    }

    /**
     * @param \ArrayObject $project
     *
     * @throws \Kunstmaan\Skylab\Exceptions\SkylabException
     */
    private function anonymizeDatabase(\ArrayObject $project)
    {
        $pdo = new \PDO(
            sprintf(
                'mysql:host=%s;dbname=%s;charset=%s;collate=%s;',
                $project['mysqlserver'],
                $project['mysqldbname_anonymized'],
                $this->reader->getCharset(),
                $this->reader->getCollate()
            ),
            $project['mysqluser'],
            $project['mysqlpass']
        );
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // Now work on the DB
        $anon = new DB($pdo);
        $anon->setConfiguration($this->reader);
        $anon->setLocale($this->reader->getLocale());

        // Execute queries before anonymization
        $preQueries = $this->reader->getPreQueries();
        if (!empty($preQueries)) {
            foreach ($preQueries as $preQuery) {
                try {
                    $this->dialogProvider->logNotice("Executing pre-query: ".$preQuery);

                    $result = $pdo->query($preQuery);
                    $result->execute();
                } catch (\Exception $e) {
                    $this->dialogProvider->logError($e->getMessage());
                }
            }
        }

        // Get tables
        $tables = $this->reader->getEntities();

        foreach ($tables as $table) {
            try {
                $result = $pdo->query("SELECT COUNT(1) FROM $table");
            } catch (\Exception $e) {
                $this->dialogProvider->logError("Could not count records in table '$table' defined in your config");
            }

            $data = $result->fetchAll(\PDO::FETCH_COLUMN);
            $total = (int) $data[0];
            if ($total === 0) {
                $this->dialogProvider->logNotice("<info>$table is empty</info>");
                continue;
            }

            $this->dialogProvider->logNotice("<info>Anonymizing $table</info>");
            $anon->processEntity($table, null, false);
        }

        // Execute queries after anonymization
        $postQueries = $this->reader->getPostQueries();
        if (!empty($postQueries)) {
            foreach ($postQueries as $postQuery) {
                try {
                    $this->dialogProvider->logNotice("Executing post-query: ".$postQuery);

                    $result = $pdo->query($postQuery);
                    $result->execute();
                } catch (\Exception $e) {
                    $this->dialogProvider->logError($e->getMessage());
                }
            }
        }

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
        $pdo = new \PDO('mysql:host='.$project["mysqlserver"].";", "root", $this->app["config"]["mysql"]["password"]);
        $pdo->exec($this->dialogProvider->logQuery("drop database if exists ".$project["mysqldbname"]));
    }

    /**
     * @param  \ArrayObject      $project
     * @param  \SimpleXMLElement $config
     *
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
        return array('base');
    }

}
