<?php
namespace Kunstmaan\Skylab;

use Symfony\Component\Finder\Finder;

/**
 * The Compiler class compiles skylab into a phar
 */
class Compiler
{
    private $version;
    private $versionDate;

    /**
     * Compiles skylab into a single phar file
     *
     * @param $version
     * @param  string            $pharFile The full path to the file to create
     * @throws \RuntimeException
     */
    public function compile($version, $pharFile = 'skylab.phar')
    {
    if (file_exists($pharFile)) {
        unlink($pharFile);
    }

    $this->version = $version;
    $date = new \DateTime();
    $date->setTimezone(new \DateTimeZone('UTC'));
    $this->versionDate = $date->format('Y-m-d H:i:s');

    $phar = new \Phar($pharFile, 0, 'skylab.phar');
    $phar->setSignatureAlgorithm(\Phar::SHA1);

    $phar->startBuffering();

    $finder = new Finder();
    $finder->files()
        ->ignoreVCS(true)
        ->name('*.php')
        ->name('*.yml')
        ->notName('Compiler.php')
        ->in(__DIR__.'/../..')
    ;

    foreach ($finder as $file) {
        $this->addFile($phar, $file);
    }

    $finder = new Finder();
    $finder->files()
        ->ignoreVCS(true)
        ->name('*.php')
        ->exclude('Tests')
        ->in(__DIR__.'/../../../vendor/')
    ;

    foreach ($finder as $file) {
        $this->addFile($phar, $file);
    }

    $this->addSkylabBin($phar);

    // Stubs
    $phar->setStub($this->getStub());

    $phar->stopBuffering();

    // disabled for interoperability with systems without gzip ext
    $phar->compressFiles(\Phar::GZ);

    $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../../LICENSE'), false);

    unset($phar);
    }

    /**
     * @param $phar
     * @param $file
     * @param bool $strip
     */
    private function addFile($phar, $file, $strip = true)
    {
    $path = strtr(str_replace(dirname(dirname(dirname(__DIR__))).DIRECTORY_SEPARATOR, '', $file->getRealPath()), '\\', '/');
    $content = file_get_contents($file);
    if ($strip) {
        $content = $this->stripWhitespace($content);
    } elseif ('LICENSE' === basename($file)) {
        $content = "\n".$content."\n";
    }

    if ($path === 'src/Kunstmaan/Skylab/Application.php') {
        $content = str_replace('@package_version@', $this->version, $content);
        $content = str_replace('@release_date@', $this->versionDate, $content);
    }

    $phar->addFromString($path, $content);
    }

    private function addSkylabBin($phar)
    {
    $content = file_get_contents(__DIR__.'/../../../skylab');
    $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
    $phar->addFromString('skylab', $content);
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param  string $source A PHP string
     * @return string The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
    if (!function_exists('token_get_all')) {
        return $source;
    }

    $output = '';
    foreach (token_get_all($source) as $token) {
        if (is_string($token)) {
        $output .= $token;
        } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
        $output .= str_repeat("\n", substr_count($token[1], "\n"));
        } elseif (T_WHITESPACE === $token[0]) {
        // reduce wide spaces
        $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
        // normalize newlines to \n
        $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
        // trim leading spaces
        $whitespace = preg_replace('{\n +}', "\n", $whitespace);
        $output .= $whitespace;
        } else {
        $output .= $token[1];
        }
    }

    return $output;
    }

    private function getStub()
    {
    $stub = <<<'EOF'
#!/usr/bin/env php
<?php

Phar::mapPhar('skylab.phar');

EOF;

    // add warning once the phar is older than 30 days
        $warningTime = time() + 30*86400;

        $stub .= "define('SKYLAB_DEV_WARNING_TIME', $warningTime);\n";

    return $stub . <<<'EOF'
require 'phar://skylab.phar/skylab';

__HALT_COMPILER();
EOF;
    }
}
