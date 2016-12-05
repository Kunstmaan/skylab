<?php

namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait UsesProviders
{

    /**
     * @var FileSystemProvider
     */
    protected $fileSystemProvider;

    /**
     * @var ProjectConfigProvider
     */
    protected $projectConfigProvider;

    /**
     * @var SkeletonProvider
     */
    protected $skeletonProvider;

    /**
     * @var ProcessProvider
     */
    protected $processProvider;

    /**
     * @var PermissionsProvider
     */
    protected $permissionsProvider;

    /**
     * @var DialogProvider
     */
    protected $dialogProvider;

    /**
     * @var RemoteProvider
     */
    protected $remoteProvider;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var bool
     */
    protected $noInteraction = false;

    /**
     * @param Application     $app
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param bool            $setupClassVars
     */
    public function setup(Application $app, InputInterface $input = null, OutputInterface $output = null, $setupClassVars = false)
    {
        $providers = array(
            'filesystem' => 'fileSystemProvider',
            'projectconfig' => 'projectConfigProvider',
            'skeleton' => 'skeletonProvider',
            'process' => 'processProvider',
            'permission' => 'permissionsProvider',
            'dialog' => 'dialogProvider',
            'remote' => 'remoteProvider',
        );
        foreach ($providers as $service => $variable) {
            $this->$variable = $app[$service];
            if ($setupClassVars) {
                $this->$variable->setup($app, $input, $output);
            }
        }
        $this->output = $output;
        $this->input = $input;
        $this->app = $app;
        $this->twig = $app["twig"];

        if ($input) {
            $this->noInteraction = (bool) $input->getOption("no-interaction");
            $app["no-interaction"] = true;
        } elseif ($app["no-interaction"]) {
            $this->noInteraction = $app["no-interaction"];
        } else {
            $this->noInteraction = false;
        }

        $app["php-fpm_enabled"] = $this->processProvider->commandExists("php-fpm".$app["php_version"]);
    }

}
