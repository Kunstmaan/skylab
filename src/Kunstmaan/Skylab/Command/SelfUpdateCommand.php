<?php
namespace Kunstmaan\Skylab\Command;

use Kunstmaan\Skylab\Helper\OutputUtil;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\RuntimeException;
use Kunstmaan\Skylab\Application;

class SelfUpdateCommand extends AbstractCommand
{
    protected function configure()
    {
	$this
	    ->setName('self-update')
	    ->setDescription('Updates skylab.phar to the latest version.')
	    ->addOption("--hideLogo", null, InputOption::VALUE_NONE, 'If set, no logo or statistics will be shown')
	;
    }

    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
	$cacheDir = '/tmp';

	$localFilename = realpath($_SERVER['argv'][0]) ?: $_SERVER['argv'][0];

	// Check if current dir is writable and if not try the cache dir from settings
	$tmpDir = is_writable(dirname($localFilename))? dirname($localFilename) : $cacheDir;
	$tempFilename = $tmpDir . '/' . basename($localFilename, '.phar').'-temp.phar';

	// check for permissions in local filesystem before start connection process
	if (!is_writable($tmpDir)) {
	    throw new RuntimeException('Skylab update failed: the "'.$tmpDir.'" directory used to download the temp file could not be written');
	}

	if (!is_writable($localFilename)) {
	    throw new RuntimeException('Skylab update failed: the "'.$localFilename. '" file could not be written');
	}

	$json = $this->getSslPage('https://api.github.com/repos/kunstmaan/skylab/releases');
	$data = json_decode($json, TRUE);

	usort($data, function($a, $b) {
	    return version_compare($a["tag_name"],$b["tag_name"]) * -1;
	});

	$latest = $data[0];
	if (version_compare(Application::VERSION, $latest["tag_name"]) < 0) {
	    OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, 'New release found: '. $latest["tag_name"] . ', updating...');
	    OutputUtil::newLine($output);

	    print_r($this->getSslPage($latest["assets"][0]["url"], $latest["assets"][0]["content_type"]));

	    file_put_contents($tempFilename, $this->getSslPage($latest["assets"][0]["url"], $latest["assets"][0]["content_type"]));

	    if (!file_exists($tempFilename)) {
		OutputUtil::logError($output, OutputInterface::VERBOSITY_NORMAL, 'The download of the new Skylab version failed for an unexpected reason');
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
		OutputUtil::logError($output, OutputInterface::VERBOSITY_NORMAL, 'The download is corrupted ('.$e->getMessage().'). Please re-run the self-update command to try again.');
		return 1;
	    }
	} else {
	    OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, 'You are running the latest release: '. $latest["tag_name"]);
	}



    /*    $protocol = extension_loaded('openssl') ? 'https' : 'http';
	$rfs = new RemoteFilesystem($this->getIO());
	$latest = trim($rfs->getContents('roderik.github.io', $protocol . '://roderik.github.io/vivaldi/version', false));

	if (Skylab::VERSION !== $latest) {
	    $output->writeln(sprintf("Updating to version <info>%s</info>.", $latest));

	    $remoteFilename = $protocol . '://roderik.github.io/vivaldi/vivaldi.phar';

	    $rfs->copy('getcomposer.org', $remoteFilename, $tempFilename);

	    if (!file_exists($tempFilename)) {
		$output->writeln('<error>The download of the new Skylab version failed for an unexpected reason');

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
		$output->writeln('<error>The download is corrupted ('.$e->getMessage().').</error>');
		$output->writeln('<error>Please re-run the self-update command to try again.</error>');
	    }
	} else {
	    $output->writeln("<info>You are using the latest Skylab version.</info>");
	}*/
    }

    private function getSslPage($url, $contentType=null) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_REFERER, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	if ($contentType) {
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: ".$contentType));
	}
	curl_setopt($ch, CURLOPT_USERAGENT, "Skylab ".Application::VERSION." (https://github.com/Kunstmaan/skylab)");
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
    }
}