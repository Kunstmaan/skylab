<?php
namespace Kunstmaan\Skylab\Command;

use CL\Slack\Exception\SlackException;
use CL\Slack\Model\Attachment;
use CL\Slack\Model\AttachmentField;
use CL\Slack\Payload\ChatDeletePayload;
use CL\Slack\Payload\ChatPostMessagePayload;
use CL\Slack\Payload\ChatPostMessagePayloadResponse;
use CL\Slack\Transport\ApiClient;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

/**
 * ExecuteCommand
 */
class ExecuteCommand extends AbstractCommand
{

    private $ts = null;
    private $channel = null;
    /** @var ApiClient */
    private $slackApiClient = null;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->addDefaults()
            ->setName('execute')
            ->setDescription('Executes a Skylab YAML file')
            ->addArgument('file', InputArgument::REQUIRED, 'The full path to the YAML file')
            ->addArgument('deploy-environment', InputArgument::OPTIONAL, 'The environment to deploy to')
            ->addOption("--skip-build", null, InputOption::VALUE_NONE, 'If set, the build steps will be skipped')
            ->addOption("--skip-tests", null, InputOption::VALUE_NONE, 'If set, the test steps will be skipped')
            ->addOption("--skip-deploy", null, InputOption::VALUE_NONE, 'If set, the deploy steps will be skipped')
            ->addOption("--debug-yml", null, InputOption::VALUE_NONE, 'If set, the resulting yml will be shown without executing it')
            ->setHelp(<<<EOT
The <info>execute</info> command will execute a Skylab YAML file, used for testing and deploying via Jenkins

<info>php skylab.phar execute /opt/skylab/templates/execute/deploy.yml</info>
EOT
            );
    }

    protected function runStep($step, $yaml, $deployEnv, $successStep = null)
    {
        if (isset($yaml[$step])) {
            $this->dialogProvider->logStep($step);

            foreach ($yaml[$step] as $list) {
                foreach ($list as $source) {
                    foreach ($source as $command) {
                        try {
                            $result = $this->processProvider->executeCommand($command, false, function ($type, $buffer) {
                                if (Process::ERR === $type) {
                                    $this->dialogProvider->logOutput($buffer, true);
                                } else {
                                    if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                                        $this->dialogProvider->logOutput($buffer, false);
                                    }
                                }
                            }, $yaml["env"]);
                            if ($result === false) {
                                $this->notifySlack("Error while running the " . $step . " phase, check the console log in Jenkins", $yaml["deploy_matrix"][$deployEnv]["project"], $deployEnv, getenv("slack_user"), array(), "#CC0000", true);
                                $this->dialogProvider->logError($step . " failed!", false);
                            }
                        } catch (\Exception $ex) {
                            $this->notifySlack("Error while running the " . $step . " phase with error: " . $ex->getMessage(), $yaml["deploy_matrix"][$deployEnv]["project"], $deployEnv, getenv("slack_user"), array(), "#CC0000", true);
                            $extra = array();
                            $tags = array();
                            $this->dialogProvider->logException($ex, $tags, $extra);
                        }
                    }
                }
            }
            if (!is_null($successStep)) {
                $this->runStep($successStep, $yaml, $deployEnv);
            }
        } else {
            return;
        }
    }

    protected function notifySlack($message, $project, $env, $user, $resolverArray, $color = "#FFCC00", $update = false)
    {
        try {
            if ($update) {
                $payload = new ChatDeletePayload();
                $payload->setSlackTimestamp($this->ts);
                $payload->setChannelId($this->channel);
                $response = $this->slackApiClient->send($payload);

                if ($response->isOk()) {
                    if ($response instanceof ChatPostMessagePayloadResponse) {
                        /** @var ChatPostMessagePayloadResponse $response */
                        $this->ts = $response->getSlackTimestamp();
                    }
                } else {
                    // something went wrong, but what?
                    // simple error (Slack's error message)
                    echo $response->getError();
                    // explained error (Slack's explanation of the error, according to the documentation)
                    echo $response->getErrorExplanation();
                }
            }

            $payload = new ChatPostMessagePayload();
            $payload->setChannel("#" . getenv("slack_channel"));
            $payload->setIconUrl("https://www.dropbox.com/s/ivrj3wcze7cwh54/masterjenkins.png?dl=1");
            $payload->setUsername("Master Jenkins");

            $attachment = new Attachment();
            $attachment->setColor($color);
            $attachment->setMrkdwnIn(array("text"));
            $attachment->setFallback("[".$project."#" . getenv("BUILD_NUMBER"). " - branch *" . getenv("GIT_BRANCH") . "* to *". $env . "* by _" . $user . "_] " . $message);
            $attachment->setText("[".$project."#" . getenv("BUILD_NUMBER"). " - branch *" . getenv("GIT_BRANCH") . "* to *". $env . "* by _" . $user ."_] " . $message . "\n<" . getenv("BUILD_URL") . "console|Jenkins Console> - <".getenv("BUILD_URL")."changes|Changes>" . (isset($resolverArray["shared_package_target"]) && file_exists($resolverArray["shared_package_target"])?" - <" . $resolverArray["shared_package_url"] . "|Download>":""));
            $payload->addAttachment($attachment);

            $response = $this->slackApiClient->send($payload);
            if ($response->isOk()) {
                if ($response instanceof ChatPostMessagePayloadResponse) {
                    /** @var ChatPostMessagePayloadResponse $response */
                    $this->ts = $response->getSlackTimestamp();
                    $this->channel = $response->getChannelId();
                }
            } else {
                // something went wrong, but what?
                // simple error (Slack's error message)
                echo $response->getError();
                // explained error (Slack's explanation of the error, according to the documentation)
                echo $response->getErrorExplanation();
            }
        } catch (SlackException $e) {
            //Ignore the slackException and continue with the deployment
        }
    }

    protected function doExecute()
    {
        if (isset($this->app["config"]["slack_api_key"])) {
            $this->slackApiClient = new ApiClient($this->app["config"]["slack_api_key"]);
        } else {
            $this->slackApiClient = new ApiClient("fake key");
        }

        $deployEnv = $this->input->getArgument('deploy-environment');
        list($yaml, $resolverArray) = $this->parseYaml();

        if ($this->input->getOption('debug-yml')) {
            print_r($yaml);
            print_r($resolverArray);
            exit(0);
        }

        if (is_null($deployEnv)) {
            $this->dialogProvider->logError("You cannot run a deploy step without an environment", true);
        }
        if (isset($yaml["deploy_matrix"][$deployEnv])) {

            //build
            if (!$this->input->getOption('skip-build')) {
                $this->notifySlack("Build started", $yaml["deploy_matrix"][$deployEnv]["project"], $deployEnv, getenv("slack_user"), $resolverArray);
                $this->runStep("before_build", $yaml, $deployEnv);
                $this->runStep("build", $yaml, $deployEnv, "after_build_success");
                $this->notifySlack("Build successful", $yaml["deploy_matrix"][$deployEnv]["project"], $deployEnv, getenv("slack_user"), $resolverArray, "#FFCC00", true);
            } else {
                $this->dialogProvider->logNotice("Build is skipped");
            }

            // test
            if (!$this->input->getOption('skip-tests')) {
                $this->notifySlack("Tests started", $yaml["deploy_matrix"][$deployEnv]["project"], $deployEnv, getenv("slack_user"), $resolverArray, "#FFCC00", true);
                $this->runStep("before_test", $yaml, $deployEnv);
                $this->runStep("test", $yaml, $deployEnv, "after_test_success");
                $this->notifySlack("Tests successful", $yaml["deploy_matrix"][$deployEnv]["project"], $deployEnv, getenv("slack_user"), $resolverArray, ($this->input->getOption('skip-deploy')?"#7CD197":"#FFCC00"), true);
            }

            // deploy
            if (!$this->input->getOption('skip-deploy')) {
                $this->notifySlack("Deploy started", $yaml["deploy_matrix"][$deployEnv]["project"], $deployEnv, getenv("slack_user"), $resolverArray, "#FFCC00", true);
                $this->runStep("before_deploy", $yaml, $deployEnv);
                $this->runStep("deploy", $yaml, $deployEnv, "after_deploy_success");
                $this->notifySlack("Deploy successful", $yaml["deploy_matrix"][$deployEnv]["project"], $deployEnv, getenv("slack_user"), $resolverArray, "#7CD197", true);
                // If failover is defined run the deploy process also on that server
                if (isset($resolverArray['deploy_failover'])) {
                    $this->dialogProvider->logWarning("Starting deploy to failover server " . $resolverArray['deploy_failover']);
                    list($yaml, $resolverArray) = $this->parseYaml(true);
                    $this->notifySlack("Deploy started on failover server", $yaml["deploy_matrix"][$deployEnv]["project"], $deployEnv, getenv("slack_user"), $resolverArray, "#FFCC00", true);
                    $this->runStep("before_deploy", $yaml, $deployEnv);
                    $this->runStep("deploy", $yaml, $deployEnv, "after_deploy_success");
                    $this->notifySlack("Deploy successful on failover server", $yaml["deploy_matrix"][$deployEnv]["project"], $deployEnv, getenv("slack_user"), $resolverArray, "#FFCC00", true);
                }
            } else {
                $this->dialogProvider->logNotice("Deploy is skipped");
            }
        } else {
            $this->dialogProvider->logError("The deploy environment " . $deployEnv . " does not exist", true);
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function parseYaml($useFailoverServer = false)
    {
        try {
            $mergedYaml = $this->buildMergedYaml();
            $resolverArray = $this->buildResolverArray($mergedYaml, $useFailoverServer);
            $resolvedYaml = $this->resolveYaml($mergedYaml, $resolverArray);
            $mergedYaml["env"]["SHELL"] = "/bin/bash";
            return array($resolvedYaml, $resolverArray);
        } catch (\Exception $ex){
            $deployEnv = $this->input->getArgument('deploy-environment');
            $this->notifySlack("Error while parding the YAML files, reason: " . $ex->getMessage(), "unknown", $deployEnv, getenv("slack_user"), array(), "#CC0000", true);
            throw $ex;
        }
    }

    /**
     * @param $mergedYaml
     * @return array
     */
    protected function buildResolverArray($mergedYaml, $useFailoverServer = false)
    {
        $resolverArray = array_merge($this->app["config"], $mergedYaml["env"]);
        $resolverArray["base_dir"] = BASE_DIR;
        $resolverArray["php_version"] = $this->app["php_version"];
        $deployEnv = $this->input->getArgument('deploy-environment');
        if (!empty($deployEnv) && isset($mergedYaml["deploy_matrix"][$deployEnv])) {
            $resolverArray = $this->collectDeploySettings($mergedYaml["deploy_matrix"][$deployEnv], "deploy", $resolverArray, $useFailoverServer);
        }
        if (isset($mergedYaml["database_source"])) {
            $dbSource = $mergedYaml["database_source"];
            if (isset($mergedYaml["deploy_matrix"][$dbSource])) {
                $resolverArray = $this->collectDeploySettings($mergedYaml["deploy_matrix"][$dbSource], "dbsource", $resolverArray, $useFailoverServer);
            }
            $resolverArray["fetch_mysql"] = "yes";
        } else {
            $resolverArray["fetch_mysql"] = "no";
        }
        $parametersFile = dirname(dirname($this->input->getArgument('file'))) . "/app/config/parameters.yml";
        if (file_exists($parametersFile) && strpos(file_get_contents($parametersFile), 'database_host') !== false) {
            $parametersYaml = $this->loadYaml($parametersFile);
            $resolverArray = array_merge($parametersYaml["parameters"], $resolverArray);
            $resolverArray["run_mysql"] = (getenv('NO_MYSQL')?"no":"yes");
        } else {
            $resolverArray["run_mysql"] = "no";
        }
        $resolverArray["webserver_engine"] = $this->app["config"]["webserver"]["engine"];
        $resolverArray["mysql_root_password"] = $this->app["config"]["mysql"]["password"];
        $resolverArray["buildtag"] = $deployEnv . "-" . $this->getRevision();
        $resolverArray["home"] = getenv("HOME");
        $resolverArray["job_name"] = getenv("JOB_NAME");
        if (key_exists("build_dir", $resolverArray)) {
            $resolverArray["build_package_target"] = $resolverArray["build_dir"] . "/".$resolverArray["job_name"]."-".$resolverArray["buildtag"].".tar.gz";
            $resolverArray["shared_package_folder"] = $resolverArray["build_dir"];
            $resolverArray["shared_package_target"] = $resolverArray["build_dir"]."/".$resolverArray["job_name"]."-".$resolverArray["deploy_timestamp"] . "-" . $resolverArray["buildtag"].".tar.gz";
        } else {
            $resolverArray["build_package_target"] = $resolverArray["home"] . "/builds/".$resolverArray["job_name"]."-".$resolverArray["buildtag"].".tar.gz";
            $resolverArray["shared_package_folder"] = "/home/projects/build/data/shared/web/uploads/";
            $resolverArray["shared_package_target"] = "/home/projects/build/data/shared/web/uploads/".$resolverArray["job_name"]."-".$resolverArray["deploy_timestamp"] . "-" . $resolverArray["buildtag"].".tar.gz";
        }
        $resolverArray["shared_package_url"] = "http://build.kunstmaan.be/uploads/".$resolverArray["job_name"]."-".$resolverArray["deploy_timestamp"] . "-" . $resolverArray["buildtag"].".tar.gz";
        $resolverArray["projects_path"] = $resolverArray["projects"]["path"];
        if ($useFailoverServer) {
            $resolverArray["remove_shared_package"] = "yes";
        } else {
            $resolverArray["remove_shared_package"] = isset($resolverArray["deploy_failover"]) ? "no" : "yes";
        }
        $resolverArray["nfs_mount_path"] = $resolverArray["projects"]["nfs_mount_path"];

        return $resolverArray;
    }

    /**
     * @param $mergedYaml
     * @param $resolverArray
     * @return mixed
     */
    protected function resolveYaml($mergedYaml, $resolverArray)
    {
        array_walk_recursive($mergedYaml, function (&$item, $key, $resolver) {
            $item = preg_replace_callback('/%%|%([^%\s]+)%/', function ($match) use ($key, $resolver) {

                // skip %%
                if (!isset($match[1])) {
                    return '%%';
                }

                $key = $match[1];

                if (isset($resolver[$key])) {
                    return $resolver[$key];
                }

                return $match[0];
            }, $item);
        }, $resolverArray);

        return $mergedYaml;
    }

    /**
     * @return array
     */
    protected function buildMergedYaml()
    {
        $ymlPath = $this->input->getArgument('file');
        $parsedYaml = $this->loadYaml($ymlPath);
        $mergedYaml = $this->handleTemplateYaml($parsedYaml);
        $mergedYaml = $this->handleResources($mergedYaml);
        return $mergedYaml;
    }

    protected function handleResources($mergedYaml)
    {

        array_walk_recursive($mergedYaml, function (&$item, $key, &$mergedYaml) {
            if ($key === "resource") {
                $resourceYaml = $this->loadYaml(BASE_DIR . "/templates/execute/" . $item);
                $item = $resourceYaml["steps"];
            }
        }, $mergedYaml);

        return $mergedYaml;
    }

    /**
     * @param $ymlPath
     * @return array
     */
    protected function loadYaml($ymlPath)
    {
        $yaml = new Parser();
        try {
            $parsedYaml = $yaml->parse(file_get_contents($ymlPath));
            return $parsedYaml;
        } catch (ParseException $e) {
            $this->dialogProvider->logError(sprintf("Unable to parse the YAML string: %s", $e->getMessage()), false);
        }
    }

    protected function handleTemplateYaml($parsedYaml)
    {
        if (isset($parsedYaml["template"])) {
            $templateYaml = $this->loadYaml(BASE_DIR . "/templates/execute/" . $parsedYaml["template"] . ".yml");
            $mergedYaml = array_merge($templateYaml, $parsedYaml);
        } else {
            $mergedYaml = $parsedYaml;
        }
        $mergedYaml["env"] = array_merge((isset($templateYaml["env"]) ? $templateYaml["env"] : array()), (isset($parsedYaml["env"]) ? $parsedYaml["env"] : array()));
        return $mergedYaml;
    }

    protected function collectDeploySettings($deploySettings, $prefix, $resolverArray, $useFailoverServer = false)
    {
        if ($prefix == 'deploy' && array_key_exists("deploy_user", $resolverArray)) {
            $resolverArray[$prefix . "_user"] = $resolverArray["deploy_user"];
            if ($useFailoverServer) {
                $resolverArray[$prefix . "_server"] = $resolverArray["deploy_user"] . "@" . $deploySettings["failover"];
            } else {
                $resolverArray[$prefix . "_server"] = $resolverArray["deploy_user"] . "@" . $deploySettings["server"];
            }
        } else {
            if ($useFailoverServer) {
                $resolverArray[$prefix . "_server"] = $deploySettings["failover"];
            } else {
                $resolverArray[$prefix . "_server"] = $deploySettings["server"];
            }
        }
        if ($useFailoverServer) {
            $resolverArray[$prefix . "_ssh-keyscan_server"] = $deploySettings["failover"];
        } else {
            $resolverArray[$prefix . "_ssh-keyscan_server"] = $deploySettings["server"];
        }
        if (isset($deploySettings["port"])) {
            $resolverArray[$prefix . "_port"] = $deploySettings["port"];
        } else {
            $resolverArray[$prefix . "_port"] = 22;
        }
        $resolverArray[$prefix . "_project"] = $deploySettings["project"];
        if (isset($deploySettings["app_path"])) {
            $resolverArray[$prefix . "_app_path"] = $deploySettings["app_path"];
        } else {
            $resolverArray[$prefix . "_app_path"] = "/ROOT";
        }
        if (isset($deploySettings["symfony_env"])) {
            $resolverArray[$prefix . "_symfony_env"] = $deploySettings["symfony_env"];
        } else {
            $resolverArray[$prefix . "_symfony_env"] = "prod";
        }
        $resolverArray[$prefix . "_timestamp"] = time();
        if (isset($deploySettings["failover"])) {
            $resolverArray[$prefix . "_failover"] = $deploySettings["failover"];
        }
        return $resolverArray;
    }

    /**
     * @return mixed
     */
    protected function getRevision()
    {
        return $this->processProvider->executeCommand('git log --pretty=format:"%h" -1');
    }

    /**
     * @return mixed
     */
    protected function getBranch()
    {
        return $this->processProvider->executeCommand('git rev-parse --abbrev-ref HEAD');
    }
}
