<?php
namespace Kunstmaan\Skylab\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * NewProjectCommand
 */
class NewProjectCommand extends AbstractCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->addDefaults()
            ->setName('new')
            ->setDescription('Create a new Skylab project')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the project. All lowercase, no spaces or special characters. Keep it short, yet descriptive')
            ->setHelp(<<<EOT
The <info>new</info> command creates a new project. It will setup the directory structure and apply the "base" skeleton
which is responsible for setting up users, permissions and ownership.

<info>php skylab.phar new</info>
<info>php skylab.phar new testproject</info>

EOT
            );
    }

    /**
     * @throws \RuntimeException
     */
    protected function DoExecute()
    {
        $projectname = $this->dialogProvider->askFor("Please enter the name of the project. All lowercase, no spaces or special characters. Keep it short, yet descriptive", 'name');
        $this->dialogProvider->logStep("Creating project $projectname");
        // Check if the project exists, do use in creating a new one with the same name.
        if ($this->fileSystemProvider->projectExists($projectname)) {
            throw new RuntimeException("A project with name $projectname already exists!");
        } else {
            $this->dialogProvider->logTask("Creating project directory for $projectname");
            $this->fileSystemProvider->createProjectDirectory($projectname, $this->output);
        }
        $project = new \ArrayObject();
        $project["name"] = $projectname;
        $project["dir"] = $this->fileSystemProvider->getProjectDirectory($projectname);
        $this->skeletonProvider->applySkeleton($project, $this->skeletonProvider->findSkeleton("base", $this->output), $this->output);
        $this->projectConfigProvider->writeProjectConfig($project, $this->output);

    }
}
