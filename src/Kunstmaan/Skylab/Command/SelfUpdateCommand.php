<?php
namespace Kunstmaan\Skylab\Command;

use Humbug\SelfUpdate\Strategy\GithubStrategy;
use Humbug\SelfUpdate\Strategy\ShaStrategy;
use Humbug\SelfUpdate\Updater;
use Humbug\SelfUpdate\VersionParser;
use Symfony\Component\Console\Input\InputOption;


class SelfUpdateCommand extends AbstractCommand
{

    const VERSION_URL = 'https://s3-eu-west-1.amazonaws.com/kunstmaan-skylab/skylab.version';
    const PHAR_URL = 'https://s3-eu-west-1.amazonaws.com/kunstmaan-skylab/skylab.phar';
    const PACKAGE_NAME = 'kunstmaan/skylab';
    const FILE_NAME = 'skylab.phar';

    protected function configure()
    {
        $this
            ->addDefaults()
            ->setName('self-update')
            ->setDescription('Update skylab.phar to most recent stable, pre-release or development build.')
            ->addOption(
                'dev',
                'd',
                InputOption::VALUE_NONE,
                'Update to most recent development build of Skylab.'
            )
            ->addOption(
                'non-dev',
                'N',
                InputOption::VALUE_NONE,
                'Update to most recent non-development (alpha/beta/stable) build of Skylab tagged on Github.'
            )
            ->addOption(
                'pre',
                'p',
                InputOption::VALUE_NONE,
                'Update to most recent pre-release version of Skylab (alpha/beta/rc) tagged on Github.'
            )
            ->addOption(
                'stable',
                's',
                InputOption::VALUE_NONE,
                'Update to most recent stable version of Skylab tagged on Github.'
            )
            ->addOption(
                'rollback',
                'r',
                InputOption::VALUE_NONE,
                'Rollback to previous version of Skylab if available on filesystem.'
            )
            ->addOption(
                'check',
                'c',
                InputOption::VALUE_NONE,
                'Checks what updates are available across all possible stability tracks.'
            )
            ->setHelp(<<<EOT
The <info>self-update</info> command will check if there is an updated skylab.phar released and updates if it is.

<info>php skylab.phar self-update</info>

EOT
            );
    }

    protected function doExecute()
    {

        $parser = new VersionParser();

        /**
         * Check for ancilliary options
         */
        if ($this->input->getOption('rollback')) {
            $this->rollback();
            return;
        }

        if ($this->input->getOption('check')) {
            $this->printAvailableUpdates();
            return;
        }

        /**
         * Update to any specified stability option
         */
        if ($this->input->getOption('dev')) {
            $this->updateToDevelopmentBuild();
            return;
        }

        if ($this->input->getOption('pre')) {
            $this->updateToPreReleaseBuild();
            return;
        }

        if ($this->input->getOption('stable')) {
            $this->updateToStableBuild();
            return;
        }

        if ($this->input->getOption('non-dev')) {
            $this->updateToMostRecentNonDevRemote();
            return;
        }

        /**
         * If current build is stable, only update to more recent stable versions if available. User may specify
         * otherwise using options.
         */
        if ($parser->isStable($this->getApplication()->getVersion())) {
            $this->updateToStableBuild();
            return;
        }

        /**
         * By default, update to most recent remote version regardless of stability.
         */
        $this->updateToMostRecentNonDevRemote();
    }

    protected function getStableUpdater()
    {
        $updater = new Updater(null, false);
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        return $this->getGithubReleasesUpdater($updater);
    }

    protected function getPreReleaseUpdater()
    {
        $updater = new Updater(null, false);
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        /** @var GithubStrategy $strategyInterface */
        $strategyInterface = $updater->getStrategy();
        $strategyInterface->setStability(GithubStrategy::UNSTABLE);
        return $this->getGithubReleasesUpdater($updater);
    }

    protected function getMostRecentNonDevUpdater()
    {
        $updater = new Updater(null, false);
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        /** @var GithubStrategy $strategyInterface */
        $strategyInterface = $updater->getStrategy();
        $strategyInterface->setStability(GithubStrategy::ANY);
        return $this->getGithubReleasesUpdater($updater);
    }

    protected function getGithubReleasesUpdater(Updater $updater)
    {
        /** @var GithubStrategy $strategyInterface */
        $strategyInterface = $updater->getStrategy();
        $strategyInterface->setPackageName(self::PACKAGE_NAME);
        $strategyInterface->setPharName(self::FILE_NAME);
        $strategyInterface->setCurrentLocalVersion($this->getApplication()->getVersion());
        return $updater;
    }

    protected function getDevelopmentUpdater()
    {
        $updater = new Updater(null, false);
        /** @var ShaStrategy $strategyInterface */
        $strategyInterface = $updater->getStrategy();
        $strategyInterface->setPharUrl(self::PHAR_URL);
        $strategyInterface->setVersionUrl(self::VERSION_URL);
        return $updater;
    }

    protected function updateToStableBuild()
    {
        $this->update($this->getStableUpdater());
    }

    protected function updateToPreReleaseBuild()
    {
        $this->update($this->getPreReleaseUpdater());
    }

    protected function updateToMostRecentNonDevRemote()
    {
        $this->update($this->getMostRecentNonDevUpdater());
    }

    protected function updateToDevelopmentBuild()
    {
        $this->update($this->getDevelopmentUpdater());
    }

    protected function update(Updater $updater)
    {
        $this->dialogProvider->logCommand("Updating Skylab");
        try {
            $result = $updater->update();

            $newVersion = $updater->getNewVersion();
            $oldVersion = $updater->getOldVersion();
            if (strlen($newVersion) == 40) {
                $newVersion = 'dev-' . $newVersion;
            }
            if (strlen($oldVersion) == 40) {
                $oldVersion = 'dev-' . $oldVersion;
            }

            if ($result) {
                $this->dialogProvider->logTask("Skylab has been updated from $oldVersion to $newVersion");
            } else {
                $this->dialogProvider->logTask("Skylab is currently up to date at $oldVersion");
            }
        } catch (\Exception $e) {
            $this->dialogProvider->logError("Error: " . $e->getMessage());
        }
        $this->dialogProvider->logNotice('You can also select update stability using --dev, --pre (alpha/beta/rc) or --stable.');
    }

    protected function rollback()
    {
        $updater = new Updater;
        try {
            $result = $updater->rollback();
            if ($result) {
                $this->dialogProvider->logTask("Skylab has been rolled back to prior version.");
            } else {
                $this->dialogProvider->logError("Rollback failed for reasons unknown.");
            }
        } catch (\Exception $e) {
            $this->dialogProvider->logError("Error: " . $e->getMessage());
        }
    }

    protected function printAvailableUpdates()
    {
        $this->printCurrentLocalVersion();
        $this->printCurrentStableVersion();
        $this->printCurrentPreReleaseVersion();
        $this->printCurrentDevVersion();
        $this->dialogProvider->logNotice('You can select update stability using --dev, --pre or --stable when self-updating.');
    }

    protected function printCurrentLocalVersion()
    {
        $this->dialogProvider->logTask("Your current local build version is: " . $this->getApplication()->getVersion());
    }

    protected function printCurrentStableVersion()
    {
        $this->printVersion($this->getStableUpdater());
    }

    protected function printCurrentPreReleaseVersion()
    {
        $this->printVersion($this->getPreReleaseUpdater());
    }

    protected function printCurrentDevVersion()
    {
        $this->printVersion($this->getDevelopmentUpdater());
    }

    protected function printVersion(Updater $updater)
    {
        $stability = 'stable';
        $strategyInterface = $updater->getStrategy();
        if ($strategyInterface instanceof ShaStrategy) {
            $stability = 'development';
        } elseif ($strategyInterface instanceof GithubStrategy
            && $strategyInterface->getStability() == GithubStrategy::UNSTABLE) {
            $stability = 'pre-release';
        }

        try {
            if ($updater->hasUpdate()) {
                $this->dialogProvider->logTask("The current $stability build available remotely is: " . $updater->getNewVersion());
            } elseif (false == $updater->getNewVersion()) {
                $this->dialogProvider->logTask("There are no $stability builds available.");
            } else {
                $this->dialogProvider->logTask("You have the current $stability build installed.");
            }
        } catch (\Exception $e) {
            $this->dialogProvider->logError("Error: " . $e->getMessage());
        }
    }
}