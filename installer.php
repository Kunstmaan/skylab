#!/usr/bin/env php
<?php

process($argv);

/**
 * processes the installer
 */
function process($argv)
{
    $check      = in_array('--check', $argv);
    $help       = in_array('--help', $argv);
    $force      = in_array('--force', $argv);
    $quiet      = in_array('--quiet', $argv);
    $installDir = false;

    // --no-ansi wins over --ansi
    if (in_array('--no-ansi', $argv)) {
        define('USE_ANSI', false);
    } elseif (in_array('--ansi', $argv)) {
        define('USE_ANSI', true);
    } else {
        // On Windows, default to no ANSI, except in ANSICON and ConEmu.
        // Everywhere else, default to ANSI if stdout is a terminal.
        define('USE_ANSI',
        (DIRECTORY_SEPARATOR == '\\')
            ? (false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI'))
            : (function_exists('posix_isatty') && posix_isatty(1))
        );
    }

    foreach ($argv as $key => $val) {
        if (0 === strpos($val, '--install-dir')) {
            if (13 === strlen($val) && isset($argv[$key+1])) {
                $installDir = trim($argv[$key+1]);
            } else {
                $installDir = trim(substr($val, 14));
            }
        }
    }

    if ($help) {
        displayHelp();
        exit(0);
    }

    $ok = checkPlatform($quiet);

    if (false !== $installDir && !is_dir($installDir)) {
        out("The defined install dir ({$installDir}) does not exist.", 'info');
        $ok = false;
    }

    if ($check) {
        exit($ok ? 0 : 1);
    }

    if ($ok || $force) {
        installComposer($installDir, $quiet);
        exit(0);
    }

    exit(1);
}

/**
 * displays the help
 */
function displayHelp()
{
    echo <<<EOF
Composer Installer
------------------
Options
--help               this help
--check              for checking environment only
--force              forces the installation
--ansi               force ANSI color output
--no-ansi            disable ANSI color output
--install-dir="..."  accepts a target installation directory

EOF;
}

/**
 * check the platform for possible issues on running composer
 */
function checkPlatform($quiet)
{
    $errors = array();
    $warnings = array();

    $iniPath = php_ini_loaded_file();
    $displayIniMessage = false;
    if ($iniPath) {
        $iniMessage = PHP_EOL.PHP_EOL.'The php.ini used by your command-line PHP is: ' . $iniPath;
    } else {
        $iniMessage = PHP_EOL.PHP_EOL.'A php.ini file does not exist. You will have to create one.';
    }
    $iniMessage .= PHP_EOL.'If you can not modify the ini file, you can also run `php -d option=value` to modify ini values on the fly. You can use -d multiple times.';

    if (ini_get('detect_unicode')) {
        $errors['unicode'] = 'On';
    }

    if (extension_loaded('suhosin')) {
        $suhosin = ini_get('suhosin.executor.include.whitelist');
        $suhosinBlacklist = ini_get('suhosin.executor.include.blacklist');
        if (false === stripos($suhosin, 'phar') && (!$suhosinBlacklist || false !== stripos($suhosinBlacklist, 'phar'))) {
            $errors['suhosin'] = $suhosin;
        }
    }

    if (!function_exists('json_decode')) {
        $errors['json'] = true;
    }

    if (!extension_loaded('Phar')) {
        $errors['phar'] = true;
    }

    if (!extension_loaded('filter')) {
        $errors['filter'] = true;
    }

    if (!extension_loaded('hash')) {
        $errors['hash'] = true;
    }

    if (!extension_loaded('ctype')) {
        $errors['ctype'] = true;
    }

    if (!ini_get('allow_url_fopen')) {
        $errors['allow_url_fopen'] = true;
    }

    if (extension_loaded('ionCube Loader') && ioncube_loader_iversion() < 40009) {
        $errors['ioncube'] = ioncube_loader_version();
    }

    if (version_compare(PHP_VERSION, '5.4.0', '<')) {
        $errors['php'] = PHP_VERSION;
    }

    if (!extension_loaded('openssl')) {
        $warnings['openssl'] = true;
    }

    if (ini_get('apc.enable_cli')) {
        $warnings['apc_cli'] = true;
    }

    ob_start();
    phpinfo(INFO_GENERAL);
    $phpinfo = ob_get_clean();
    if (preg_match('{Configure Command(?: *</td><td class="v">| *=> *)(.*?)(?:</td>|$)}m', $phpinfo, $match)) {
        $configure = $match[1];

        if (false !== strpos($configure, '--enable-sigchild')) {
            $warnings['sigchild'] = true;
        }

        if (false !== strpos($configure, '--with-curlwrappers')) {
            $warnings['curlwrappers'] = true;
        }
    }

    if (!empty($errors)) {
        out("Some settings on your machine make Skylab unable to work properly.", 'error');

        out('Make sure that you fix the issues listed below and run this script again:', 'error');
        foreach ($errors as $error => $current) {
            switch ($error) {
                case 'json':
                    $text = PHP_EOL."The json extension is missing.".PHP_EOL;
                    $text .= "Install it or recompile php without --disable-json";
                    break;

                case 'phar':
                    $text = PHP_EOL."The phar extension is missing.".PHP_EOL;
                    $text .= "Install it or recompile php without --disable-phar";
                    break;

                case 'filter':
                    $text = PHP_EOL."The filter extension is missing.".PHP_EOL;
                    $text .= "Install it or recompile php without --disable-filter";
                    break;

                case 'hash':
                    $text = PHP_EOL."The hash extension is missing.".PHP_EOL;
                    $text .= "Install it or recompile php without --disable-hash";
                    break;

                case 'ctype':
                    $text = PHP_EOL."The ctype extension is missing.".PHP_EOL;
                    $text .= "Install it or recompile php without --disable-ctype";
                    break;

                case 'unicode':
                    $text = PHP_EOL."The detect_unicode setting must be disabled.".PHP_EOL;
                    $text .= "Add the following to the end of your `php.ini`:".PHP_EOL;
                    $text .= "    detect_unicode = Off";
                    $displayIniMessage = true;
                    break;

                case 'suhosin':
                    $text = PHP_EOL."The suhosin.executor.include.whitelist setting is incorrect.".PHP_EOL;
                    $text .= "Add the following to the end of your `php.ini` or suhosin.ini (Example path [for Debian]: /etc/php5/cli/conf.d/suhosin.ini):".PHP_EOL;
                    $text .= "    suhosin.executor.include.whitelist = phar ".$current;
                    $displayIniMessage = true;
                    break;

                case 'php':
                    $text = PHP_EOL."Your PHP ({$current}) is too old, you must upgrade to PHP 5.4 or higher.";
                    break;

                case 'allow_url_fopen':
                    $text = PHP_EOL."The allow_url_fopen setting is incorrect.".PHP_EOL;
                    $text .= "Add the following to the end of your `php.ini`:".PHP_EOL;
                    $text .= "    allow_url_fopen = On";
                    $displayIniMessage = true;
                    break;

                case 'ioncube':
                    $text = PHP_EOL."Your ionCube Loader extension ($current) is incompatible with Phar files.".PHP_EOL;
                    $text .= "Upgrade to ionCube 4.0.9 or higher or remove this line (path may be different) from your `php.ini` to disable it:".PHP_EOL;
                    $text .= "    zend_extension = /usr/lib/php5/20090626+lfs/ioncube_loader_lin_5.3.so";
                    $displayIniMessage = true;
                    break;
            }
            if ($displayIniMessage) {
                $text .= $iniMessage;
            }
            out($text, 'info');
        }

        out('');

        return false;
    }

    if (!empty($warnings)) {
        out("Some settings on your machine may cause stability issues with Skylab.", 'error');

        out('If you encounter issues, try to change the following:', 'error');
        foreach ($warnings as $warning => $current) {
            switch ($warning) {
                case 'apc_cli':
                    $text = PHP_EOL."The apc.enable_cli setting is incorrect.".PHP_EOL;
                    $text .= "Add the following to the end of your `php.ini`:".PHP_EOL;
                    $text .= "    apc.enable_cli = Off";
                    $displayIniMessage = true;
                    break;

                case 'sigchild':
                    $text = PHP_EOL."PHP was compiled with --enable-sigchild which can cause issues on some platforms.".PHP_EOL;
                    $text .= "Recompile it without this flag if possible, see also:".PHP_EOL;
                    $text .= "    https://bugs.php.net/bug.php?id=22999";
                    break;

                case 'curlwrappers':
                    $text = PHP_EOL."PHP was compiled with --with-curlwrappers which will cause issues with HTTP authentication and GitHub.".PHP_EOL;
                    $text .= "Recompile it without this flag if possible";
                    break;

                case 'openssl':
                    $text = PHP_EOL."The openssl extension is missing, which will reduce the security and stability of Composer.".PHP_EOL;
                    $text .= "If possible you should enable it or recompile php with --with-openssl";
                    break;

                case 'php':
                    $text = PHP_EOL."Your PHP ({$current}) is quite old, upgrading to PHP 5.4 or higher is recommended.".PHP_EOL;
                    $text .= "Skylab works with 5.4 for most people, but there might be edge case issues.";
                    break;
            }
            if ($displayIniMessage) {
                $text .= $iniMessage;
            }
            out($text, 'info');
        }

        out('');

        return true;
    }

    if (!$quiet) {
        out("All settings correct for using Composer", 'success');
    }

    return true;
}

/**
 * installs composer to the current working directory
 */
function installComposer($installDir, $quiet)
{
    $installPath = (is_dir($installDir) ? rtrim($installDir, '/').'/' : '') . 'skylab.phar';
    $installDir = realpath($installDir) ? realpath($installDir) : getcwd();
    $file       = $installDir.DIRECTORY_SEPARATOR.'skylab.phar';

    if (is_readable($file)) {
        @unlink($file);
    }

    $retries = 3;
    while ($retries--) {
        if (!$quiet) {
            out("Downloading...", 'info');
        }

        $json = curl('https://api.github.com/repos/kunstmaan/skylab/releases');
        $data = json_decode($json, TRUE);
        $latest = $data[0];

        $errorHandler = new ErrorHandler();
        set_error_handler(array($errorHandler, 'handleError'));

        curl($latest["assets"][0]["url"], $latest["assets"][0]["content_type"], $file);

        restore_error_handler();
        if ($errorHandler->message) {
            continue;
        }

        try {
            // test the phar validity
            $phar = new Phar($file);
            // free the variable to unlock the file
            unset($phar);
            break;
        } catch (Exception $e) {
            if (!$e instanceof UnexpectedValueException && !$e instanceof PharException) {
                throw $e;
            }
            unlink($file);
            if ($retries) {
                if (!$quiet) {
                    out('The download is corrupt, retrying...', 'error');
                }
            } else {
                out('The download is corrupt ('.$e->getMessage().'), aborting.', 'error');
                exit(1);
            }
        }
    }

    if ($errorHandler->message) {
        out('The download failed repeatedly, aborting.', 'error');
        exit(1);
    }

    chmod($file, 0755);

    if (!$quiet) {
        out(PHP_EOL."Skylab successfully installed to: " . $file, 'success', false);
        out(PHP_EOL."Use it: php $installPath", 'info');
    }
}

/**
 * colorize output
 */
function out($text, $color = null, $newLine = true)
{
    $styles = array(
        'success' => "\033[0;32m%s\033[0m",
        'error' => "\033[31;31m%s\033[0m",
        'info' => "\033[33;33m%s\033[0m"
    );

    $format = '%s';

    if (isset($styles[$color]) && USE_ANSI) {
        $format = $styles[$color];
    }

    if ($newLine) {
        $format .= PHP_EOL;
    }

    printf($format, $text);
}

/**
 * @param $url
 * @param  string $contentType
 * @param  string $filename
 * @return mixed
 */
function curl($url, $contentType = null, $filename = null)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    if ($contentType) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: " . $contentType));
    }
    $tmpfile = setDownloadHeaders($filename, $ch);
    curl_setopt($ch, CURLOPT_USERAGENT, "Skylab Installer (https://github.com/Kunstmaan/skylab)");
    $result = curl_exec($ch);
    curl_close($ch);
    if ($filename) {
        closeFile($tmpfile);
    } else {
        return $result;
    }

    return false;
}

function setDownloadHeaders($filename, $ch)
{
    if ($filename) {
        $tempFP = fopen($filename, 'w+');
        curl_setopt($ch, CURLOPT_FILE, $tempFP);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

        return $tempFP;
    }

    return false;
}

function closeFile($tempFP)
{
    if (!$tempFP) {
        fclose($tempFP);
    }
}

class ErrorHandler
{
    public $message = '';

    public function handleError($code, $msg)
    {
        if ($this->message) {
            $this->message .= "\n";
        }
        $this->message .= preg_replace('{^copy\(.*?\): }', '', $msg);
    }
}
