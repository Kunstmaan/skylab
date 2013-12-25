<?php
namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Cilex\ServiceProviderInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * AbstractProvider
 */
abstract class AbstractProvider implements ServiceProviderInterface
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
     * @var Application
     */
    protected $app;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Input\InputInterface $input
     */
    public function setUpClassVars(OutputInterface $output, InputInterface $input)
    {
        $this->input = $input;
        $this->output = $output;

        $providers = array(
            'filesystem' => 'fileSystemProvider',
            'projectconfig' => 'projectConfigProvider',
            'skeleton' => 'skeletonProvider',
            'process' => 'processProvider',
            'permission' => 'permissionsProvider',
            'dialog' => 'dialogProvider',
        );
        foreach ($providers as $service => $variable) {
            $this->$variable = $this->app[$service];
        }
    }

}
