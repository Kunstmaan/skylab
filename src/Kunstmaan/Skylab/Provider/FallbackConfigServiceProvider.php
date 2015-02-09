<?php

namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Symfony\Component\Yaml;

class FallbackConfigServiceProvider extends AbstractProvider
{

    public function register(Application $app)
    {
        $this->app = $app;

        $app['config'] = $app->share(
            function () use ($app) {
                $config = array();
                foreach ($app['config.paths'] as $path) {
                    if (!file_exists($path)) {
                        continue;
                    }
                    $parser = new Yaml\Parser();
                    $result = @file_get_contents($path);
                    if($result === FALSE) {
                        $result = $app['process']->executeSudoCommand("cat " . $path, true);
                    }
                    $config = array_replace_recursive($config, $parser->parse($result));
                }

                return $config;
            }
        );
    }
}
