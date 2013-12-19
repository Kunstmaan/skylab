<?php

namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Cilex\ServiceProviderInterface;
use Symfony\Component\Yaml;

class FallbackConfigServiceProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
        $app['config'] = $app->share(
            function () use ($app) {
                $config = array();
        foreach ($app['config.paths'] as $path) {
            if (!file_exists($path)) {
                        continue;
                    }
                    $parser = new Yaml\Parser();
                    $result = $parser->parse(file_get_contents($path));
                    $config = array_merge_recursive($config, $result);
                }

                return $config;
            }
        );
    }
}
