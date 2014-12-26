<?php
namespace Kunstmaan\Skylab\Skeleton;
use Symfony\Component\Finder\Finder;

/**
 * PostgresQLSkeleton
 */
class PostgreSQLSkeleton extends AbstractSkeleton
{

    const NAME = "postgres";

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
        $project["dbuser"] = $this->dialogProvider->askFor("Enter a PostgreSQL username", null, $project["name"]);
        $pwgen = new \PWGen();
        $project["dbpass"] = $this->dialogProvider->askFor("Enter a PostgreSQL password", null, $pwgen->generate());
        $project["dbname"] = $this->dialogProvider->askFor("Enter a PostgreSQL databasename", null, $project["name"]);
        $project["dbserver"] = $this->dialogProvider->askFor("Enter a PostgreSQL server host", null, "localhost");
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
        $this->permissionsProvider->createGroupIfNeeded($project["name"]);
        $this->permissionsProvider->createUserIfNeeded($project["name"], $project["name"]);
        
        try {
            new \PDO(
                $this->dialogProvider->logQuery(
                    'pgsql:host=' . $project["dbserver"] . ';dbname=' . $project["dbname"],
                    array(
                        "user" =>$project["dbuser"],
                        "password" => $project["dbpass"]
                        )
                    ),
                $project["dbuser"],
                $project["dbpass"]
            );
        } catch (\PDOException $exLoginTest) {
            $this->dialogProvider->logNotice("Cannot connect as " . $project["dbuser"] . ", lets test if the database exists (" . $exLoginTest->getMessage() . ")");
            try {
                new \PDO(
                    $this->dialogProvider->logQuery(
                        'pgsql:host=' . $project["dbserver"] . ';dbname=' . $project["dbname"],
                        array(
                            "user" =>$this->app["config"]["postgresql"]["user"],
                            "password" => $this->app["config"]["postgresql"]["password"]
                            )
                        ),
                    $this->app["config"]["postgresql"]["user"],
                    $this->app["config"]["postgresql"]["password"]
                );
                $this->dialogProvider->logNotice("Database " . $project["dbname"] . " exists!");
            } catch (\PDOException $exDBTest) {
                $this->dialogProvider->logNotice("Cannot connect to the " . $project["dbname"] . " database as " .$this->app["config"]["postgresql"]["user"]. " as well, lets create it. (" . $exDBTest->getMessage() . ")");
                $backupDir = $this->fileSystemProvider->getProjectDirectory($project["name"]) . "/backup/";
                $pdo = new \PDO(
                    $this->dialogProvider->logQuery(
                        'pgsql:host=' . $project["dbserver"] . ";dbname=template1",
                        array(
                            "user" =>$this->app["config"]["postgresql"]["user"],
                            "password" => $this->app["config"]["postgresql"]["password"]
                            )
                        ),
                    $this->app["config"]["postgresql"]["user"],
                    $this->app["config"]["postgresql"]["password"]
                );
                $pdo->exec($this->dialogProvider->logQuery("create user " . $project["dbuser"]));
                $pdo->exec($this->dialogProvider->logQuery("alter user ".$project["dbuser"]." with password '".$project["dbpass"]."'"));
                $pdo->exec($this->dialogProvider->logQuery("create database " . $project["dbname"] . " with owner " . $project["dbuser"] . " encoding 'UNICODE'"));
                $finder = new Finder();
                $finder->files()->in($backupDir)->name("postgres-custom.dump");
                if (count(iterator_to_array($finder)) > 0) {
                    $this->processProvider->executeSudoCommand("PGOPTIONS='-c maintenance_work_mem=64MB' pg_restore --disable-triggers -n public -j 4 -Fc ".$backupDir."/postgres-custom.dump -d ". $project["dbname"], false, $project["dbuser"]);
                }
            }
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
        $this->fileSystemProvider->createDirectory($project, $backupDir);
        if (is_file($backupDir . '/postgres-custom.dump')) {
            $this->processProvider->executeSudoCommand('rm -f ' . $backupDir . '/postgres-custom.previous.dump');
            $this->processProvider->executeSudoCommand('mv ' . $backupDir . '/postgres-custom.dump ' . $backupDir . '/postgres-custom.previous.dump');
        }
        $this->processProvider->executeSudoCommand("PGOPTIONS='-c maintenance_work_mem=64MB' pg_dump -Fc -f ".$backupDir."/postgres-custom.dump ".$project["dbname"], false, $project["dbuser"]);
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
        $pdo = new \PDO(
                $this->dialogProvider->logQuery(
                    'pgsql:host=' . $project["dbserver"] . ";dbname=template1",
                    array(
                        "user" =>$this->app["config"]["postgresql"]["user"],
                        "password" => $this->app["config"]["postgresql"]["password"]
                        )
                    ),
                $this->app["config"]["postgresql"]["user"],
                $this->app["config"]["postgresql"]["password"]
            );
            $pdo->exec($this->dialogProvider->logQuery("drop user " . $project["dbuser"]));
            $pdo->exec($this->dialogProvider->logQuery("drop database " . $project["dbname"]));
    }

    /**
     * @param  \ArrayObject      $project
     * @param  \SimpleXMLElement $config
     * @return \SimpleXMLElement
     */
    public function writeConfig(\ArrayObject $project, \SimpleXMLElement $config)
    {
        $config = $this->projectConfigProvider->addVar($config, 'project.dbuser', $project["dbuser"]);
        $config = $this->projectConfigProvider->addVar($config, 'project.dbpass', $project["dbpass"]);
        $config = $this->projectConfigProvider->addVar($config, 'project.dbname', $project["dbname"]);
        $config = $this->projectConfigProvider->addVar($config, 'project.dbserver', $project["dbserver"]);

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
