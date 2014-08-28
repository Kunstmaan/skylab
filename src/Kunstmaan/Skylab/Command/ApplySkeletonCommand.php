<?php
namespace Kunstmaan\Skylab\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * ApplySkeletonCommand
 */
class ApplySkeletonCommand extends AbstractCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->addDefaults()
            ->setName('apply')
            ->setDescription('Apply a skeleton to a Skylab project')
            ->addArgument('project', InputArgument::OPTIONAL, 'The name of the kServer project')
            ->addArgument('skeleton', InputArgument::OPTIONAL, 'The name of the skeleton')
            ->addOption("list", "l", InputOption::VALUE_NONE, 'Lists all available skeletons')
            ->setHelp(<<<EOT
The <info>apply</info> command applies a skeleton, and all it's dependencies to a project. It will run the "create"
method in the skeleton to setup all the requirements for that skeleton.

<info>php skylab.phar apply -l</info>                      # Lists all available skeletons
<info>php skylab.phar apply</info>                         # Will ask for a project and skeleton to apply
<info>php skylab.phar apply testproject anacron</info>     # Will apply the anacron skeleton to testproject

EOT
            );
    }

    /**
     * @throws \RuntimeException
     */
    protected function doExecute()
    {
        if ($this->input->getOption('list')) {
            $this->skeletonProvider->listSkeletons();
            return;
        }
        $projectname = $this->dialogProvider->askFor("Please enter the name of the project", 'project');
        // Check if the project exists, do use in creating a new one with the same name.
        if (!$this->fileSystemProvider->projectExists($projectname)) {
            throw new RuntimeException("A project with name $projectname should already exists!");
        }
        $skeletonname = $this->dialogProvider->askFor("Please enter the name of the skeleton", 'skeleton');
        $theSkeleton = $this->skeletonProvider->findSkeleton($skeletonname);
        $project = $this->projectConfigProvider->loadProjectConfig($projectname);
        $this->skeletonProvider->applySkeleton($project, $theSkeleton);
        $this->projectConfigProvider->writeProjectConfig($project);
    }

}
