<?php

namespace Kunstmaan\Skylab\Provider;

use Pimple\Container;
use Symfony\Component\Yaml;

/**
 * Class FallbackConfigServiceProvider
 *
 * @package Kunstmaan\Skylab\Provider
 */
class FallbackConfigServiceProvider extends AbstractProvider
{

    public function register(Container $app)
    {
        $this->app = $app;

        $app['config'] = function ($app) {
            $config = [];
            foreach ($app['config.paths'] as $path) {
                if (!file_exists($path)) {
                    continue;
                }
                $parser = new Yaml\Parser();
                $result = @file_get_contents($path);
                if ($result === false) {
                    $result = $app['process']->executeSudoCommand("cat ".$path, true);
                }
                $config = array_replace_recursive($config, $parser->parse($result));
            }

            return $config;
        };
    }
}
