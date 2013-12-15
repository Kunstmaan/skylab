<?php

namespace Kunstmaan\Skylab;

use Cilex\Provider\Console\ContainerAwareApplication as BaseApplication;

class Application extends BaseApplication {

    public static $logo = '<info>
  ___________           .__        ___.
 /   _____/  | _____.__.|  | _____ \_ |__
 \_____  \|  |/ <   |  ||  | \__  \ | __ \
 /        \    < \___  ||  |__/ __ \| \_\ \
/_______  /__|_ \/ ____||____(____  /___  /
        \/     \/\/               \/    \/
~~~~~~~~~~~~~~~~~~~~~~~~~~~ by Kunstmaan.be
</info>
';

    public function getHelp()
    {
        return self::$logo . parent::getHelp();
    }

}