<?php
namespace Kunstmaan\Skylab\Command;

use Kunstmaan\Skylab\Application;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Exception\RuntimeException;

class SelfUpdateCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->addDefaults()
            ->setName('self-update')
            ->setDescription('Updates skylab.phar to the latest version.');
    }

    /**
     * @return int
     * @throws \Symfony\Component\Yaml\Exception\RuntimeException
     * @throws \Exception
     */
    protected function doExecute()
    {
        $cacheDir = sys_get_temp_dir();

        $localFilename = realpath($_SERVER['argv'][0]) ? : $_SERVER['argv'][0];

        // Check if current dir is writable and if not try the cache dir from settings
        $tmpDir = is_writable(dirname($localFilename)) ? dirname($localFilename) : $cacheDir;
        $tempFilename = $tmpDir . '/' . basename($localFilename, '.phar') . '-temp.phar';

        // check for permissions in local filesystem before start connection process
        if (!is_writable($tmpDir)) {
            throw new RuntimeException('Skylab update failed: the "' . $tmpDir . '" directory used to download the temp file could not be written');
        }

        if (!is_writable($localFilename)) {
            throw new RuntimeException('Skylab update failed: the "' . $localFilename . '" file could not be written');
        }

        $json = $this->remoteProvider->curl('https://api.github.com/repos/kunstmaan/skylab/releases');
        $data = json_decode($json, TRUE);

        usort($data, function ($a, $b) {
            return version_compare($a["tag_name"], $b["tag_name"]) * -1;
        });

        $latest = $data[0];
        if (version_compare(Application::VERSION, $latest["tag_name"]) < 0) {
            $this->dialogProvider->logTask('New release found: ' . $latest["tag_name"] . ', updating...');
            $this->remoteProvider->curl($latest["assets"][0]["url"], $latest["assets"][0]["content_type"], $tempFilename);
            if (!file_exists($tempFilename)) {
                $this->dialogProvider->logError('The download of the new Skylab version failed for an unexpected reason');
                return 1;
            }
            try {
                @chmod($tempFilename, 0777 & ~umask());
                // test the phar validity
                $phar = new \Phar($tempFilename);
                // free the variable to unlock the file
                unset($phar);
                rename($tempFilename, $localFilename);
            } catch (\Exception $e) {
                @unlink($tempFilename);
                if (!$e instanceof \UnexpectedValueException && !$e instanceof \PharException) {
                    throw $e;
                }
                $this->dialogProvider->logError('The download is corrupted (' . $e->getMessage() . '). Please re-run the self-update command to try again.');

                return 1;
            }
        } else {
            $this->dialogProvider->logTask('You are running the latest release: ' . $latest["tag_name"]);
        }
        return 0;
    }


}
