<?php
namespace Kunstmaan\Skylab\Command;

use CL\Slack\Model\Attachment;
use CL\Slack\Model\AttachmentField;
use CL\Slack\Payload\ChatDeletePayload;
use CL\Slack\Payload\ChatPostMessagePayload;
use CL\Slack\Payload\ChatPostMessagePayloadResponse;
use CL\Slack\Transport\ApiClient;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
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
                                    $this->dialogProvider->logOutput($buffer, false);
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
                exit(1);
            }
        }

        $payload = new ChatPostMessagePayload();
        $payload->setChannel("#" . getenv("slack_channel"));
        $payload->setIconUrl("https://www.dropbox.com/s/ivrj3wcze7cwh54/masterjenkins.png?dl=1");
        $payload->setUsername("Master Jenkins");

        $attachment = new Attachment();
        $attachment->setColor($color);
        $attachment->setFallback("[#" . getenv("BUILD_NUMBER") . "] " . $message . " for " . $project . " in " . $env . " by @" . $user);
        $attachment->setText("[#" . getenv("BUILD_NUMBER") . "] " . $message . " for \"" . $project . "\" in \"" . $env . "\" by <@" . $user . ">\n<" . getenv("BUILD_URL") . "/console|Jenkins Console>" . (isset($resolverArray["shared_package_target"]) && file_exists($resolverArray["shared_package_target"])?" - <" . $resolverArray["shared_package_url"] . "|Download>":""));
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
            exit(1);
        }
    }

    protected function doExecute()
    {
        if (isset($this->app["config"]["slack_api_key"])) {
            $this->slackApiClient = new ApiClient($this->app["config"]["slack_api_key"]);
        } else {
            $this->slackApiClient = new ApiClient("fake key");
        }

        $this->processProvider->executeCommand("git checkout master && git pull && git reset && git clean -d -f");

        $deployEnv = $this->input->getArgument('deploy-environment');

        if (!file_exists($this->input->getArgument('file'))) {
            $this->processProvider->executeCommand("git checkout develop && git pull && git reset && git clean -d -f");
            if (!file_exists($this->input->getArgument('file'))) {
                $this->notifySlack("Both master and develop are missing " . $this->input->getArgument('file'), "unknown", array(), "#CC0000", true);
                $this->dialogProvider->logError("Both master and develop are missing " . $this->input->getArgument('file'), false);
            }
        }

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
            $this->processProvider->executeCommand("git checkout ". $yaml["deploy_matrix"][$deployEnv]["branch"]." && git pull && git reset && git clean -d -f");

            //build
            $this->notifySlack("Building \"" . $yaml["deploy_matrix"][$deployEnv]["branch"] . "\"", $yaml["deploy_matrix"][$deployEnv]["project"], $deployEnv, getenv("slack_user"), $resolverArray);
            $this->runStep("before_build", $yaml, $deployEnv);
            $this->runStep("build", $yaml, $deployEnv, "after_build_success");
            $this->notifySlack("Built \"" . $yaml["deploy_matrix"][$deployEnv]["branch"] . "\"", $yaml["deploy_matrix"][$deployEnv]["project"], $deployEnv, getenv("slack_user"), $resolverArray, "#FFCC00", true);

            // test
            if (!$this->input->getOption('skip-tests')) {
                $this->notifySlack("Testing \"" . $yaml["deploy_matrix"][$deployEnv]["branch"] . "\"", $yaml["deploy_matrix"][$deployEnv]["project"], $deployEnv, getenv("slack_user"), $resolverArray, "#FFCC00", true);
                $this->runStep("before_test", $yaml, $deployEnv);
                $this->runStep("test", $yaml, $deployEnv, "after_test_success");
                $this->notifySlack("Tested \"" . $yaml["deploy_matrix"][$deployEnv]["branch"] . "\"", $yaml["deploy_matrix"][$deployEnv]["project"], $deployEnv, getenv("slack_user"), $resolverArray, "#FFCC00", true);
            }

            // deploy
            if (!$this->input->getOption('skip-deploy')) {
                $this->notifySlack("Deploying \"" . $yaml["deploy_matrix"][$deployEnv]["branch"] . "\"", $yaml["deploy_matrix"][$deployEnv]["project"], $deployEnv, getenv("slack_user"), $resolverArray, "#FFCC00", true);
                $this->runStep("before_deploy", $yaml, $deployEnv);
                $this->runStep("deploy", $yaml, $deployEnv, "after_deploy_success");
                $this->notifySlack("Deployed \"" . $yaml["deploy_matrix"][$deployEnv]["branch"] . "\"", $yaml["deploy_matrix"][$deployEnv]["project"], $deployEnv, getenv("slack_user"), $resolverArray, "#7CD197", true);
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
    protected function parseYaml()
    {
        try {
            $mergedYaml = $this->buildMergedYaml();
            $resolverArray = $this->buildResolverArray($mergedYaml);
            $resolvedYaml = $this->resolveYaml($mergedYaml, $resolverArray);
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
    protected function buildResolverArray($mergedYaml)
    {
        $resolverArray = array_merge($this->app["config"], $mergedYaml["env"]);
        $resolverArray["base_dir"] = BASE_DIR;
        $deployEnv = $this->input->getArgument('deploy-environment');
        if (!empty($deployEnv) && isset($mergedYaml["deploy_matrix"][$deployEnv])) {
            $resolverArray = $this->collectDeploySettings($mergedYaml["deploy_matrix"][$deployEnv], "deploy", $resolverArray);
        }
        if (isset($mergedYaml["database_source"])) {
            $dbSource = $mergedYaml["database_source"];
            if (isset($mergedYaml["deploy_matrix"][$dbSource])) {
                $resolverArray = $this->collectDeploySettings($mergedYaml["deploy_matrix"][$dbSource], "dbsource", $resolverArray);
            }
            $resolverArray["fetch_mysql"] = "yes";
        } else {
            $resolverArray["fetch_mysql"] = "no";
        }
        $parametersFile = dirname(dirname($this->input->getArgument('file'))) . "/app/config/parameters.yml";
        if (file_exists($parametersFile)) {
            $parametersYaml = $this->loadYaml($parametersFile);
            $resolverArray = array_merge($parametersYaml["parameters"], $resolverArray);
            $resolverArray["run_mysql"] = "yes";
        } else {
            $resolverArray["run_mysql"] = "no";
        }
        $resolverArray["mysql_root_password"] = $this->app["config"]["mysql"]["password"];
        $resolverArray["buildtag"] = $deployEnv . "-" . $this->processProvider->executeCommand('git log --pretty=format:"%h" -1');
        $resolverArray["home"] = getenv("HOME");
        $resolverArray["job_name"] = getenv("JOB_NAME");
        $resolverArray["build_package_target"] = $resolverArray["home"] . "/builds/".$resolverArray["job_name"]."-".$resolverArray["buildtag"].".tar.gz";
        $resolverArray["shared_package_folder"] = "/home/projects/build/data/shared/web/uploads/";
        $resolverArray["shared_package_target"] = "/home/projects/build/data/shared/web/uploads/".$resolverArray["job_name"]."-".$resolverArray["deploy_timestamp"] . "-" . $resolverArray["buildtag"].".tar.gz";
        $resolverArray["shared_package_url"] = "http://build.kunstmaan.be/uploads/".$resolverArray["job_name"]."-".$resolverArray["buildtag"].".tar.gz";
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

    protected function collectDeploySettings($deploySettings, $prefix, $resolverArray)
    {
        $resolverArray[$prefix . "_server"] = $deploySettings["server"];
        if (isset($deploySettings["port"])) {
            $resolverArray[$prefix . "_port"] = $deploySettings["port"];
        } else {
            $resolverArray[$prefix . "_port"] = 22;
        }
        $resolverArray[$prefix . "_branch"] = $deploySettings["branch"];
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
        return $resolverArray;
    }
}
