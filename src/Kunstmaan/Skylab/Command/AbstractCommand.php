<?php
namespace Kunstmaan\Skylab\Command;

use Cilex\Command\Command;
use Kunstmaan\Skylab\Helper\OutputUtil;
use Kunstmaan\Skylab\Provider\DialogProvider;
use Kunstmaan\Skylab\Provider\FileSystemProvider;
use Kunstmaan\Skylab\Provider\PermissionsProvider;
use Kunstmaan\Skylab\Provider\ProcessProvider;
use Kunstmaan\Skylab\Provider\ProjectConfigProvider;
use Kunstmaan\Skylab\Provider\SkeletonProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{

    /**
     * @var FileSystemProvider
     */
    protected $filesystem;

    /**
     * @var ProjectConfigProvider
     */
    protected $projectConfig;

    /**
     * @var SkeletonProvider
     */
    protected $skeleton;

    /**
     * @var ProcessProvider
     */
    protected $process;

    /**
     * @var PermissionsProvider
     */
    protected $permission;

    /**
     * @var DialogProvider
     */
    protected $dialog;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
	$this->filesystem = $this->getService('filesystem');
	$this->projectConfig = $this->getService('projectconfig');
	$this->skeleton = $this->getService('skeleton');
	$this->process = $this->getService('process');
	$this->permission = $this->getService('permission');
	$this->dialog = $this->getService('dialog');

	OutputUtil::logo($output, OutputInterface::VERBOSITY_NORMAL, "Executing " . get_class($this));
	$this->doExecute($input, $output);
	$app = $this->getContainer();
	OutputUtil::logStatistics($output, OutputInterface::VERBOSITY_NORMAL, $app['skylab.starttime']);
    }

    abstract protected function doExecute(InputInterface $input, OutputInterface $output);


}