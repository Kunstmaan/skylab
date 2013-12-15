<?php
namespace Kunstmaan\Skylab\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SetPermissionsCommand
 */
class SetPermissionsCommand extends AbstractCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
	$this
	    ->setName('permissions')
	    ->setDescription('Set the permissions of a kServer project')
	    ->addArgument('name', InputArgument::REQUIRED, 'The name of the project');
    }

    /**
     * @param InputInterface $input The command inputstream
     * @param OutputInterface $output The command outputstream
     *
     * @return int|void
     *
     * @throws \RuntimeException
     */
    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
//        $projectname = $input->getArgument('name');
//
//        if (!$this->filesystem->projectExists($projectname)) {
//            throw new RuntimeException("The $projectname project does not exist.");
//        }
//
//        OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Setting permissions on project $projectname");
//
//        $baseSkeleton =  $this->skeleton->findSkeleton("base");
//        $project = $this->projectConfig->loadProjectConfig($projectname, $output);
//        $baseSkeleton->permissions($this->getContainer(), $project, $output);
    }
}