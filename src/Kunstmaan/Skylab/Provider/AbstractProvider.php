<?php

namespace Kunstmaan\Skylab\Provider;

use Pimple\ServiceProviderInterface;

/**
 * Class AbstractProvider
 *
 * @package Kunstmaan\Skylab\Provider
 */
abstract class AbstractProvider implements ServiceProviderInterface
{
    use UsesProviders;
}
