<?php

namespace Kunstmaan\Skylab\Provider;

use Cilex\Application;
use Kunstmaan\Skylab\Exceptions\AccessDeniedException;
use Pimple\Container;

/**
 * Class RemoteProvider
 *
 * @package Kunstmaan\Skylab\Provider
 */
class RemoteProvider extends AbstractProvider
{

    /**
     * Registers services on the given app.
     *
     * @param Container $app An Application instance
     */
    public function register(Container $app)
    {
        $app['remote'] = $this;
        $this->app = $app;
    }

    /**
     * @param         $url
     * @param  string $contentType
     * @param  string $filename
     *
     * @return string
     */
    public function curl($url, $contentType = null, $filename = null, $cacheTimeInSeconds = 0, $username = null, $password = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($username && $password) {
            curl_setopt($ch, CURLOPT_USERPWD, $username.":".$password);
        }
        if ($contentType) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Accept: ".$contentType]);
        }
        $tmpfile = $this->setDownloadHeaders($filename, $ch);
        curl_setopt($ch, CURLOPT_USERAGENT, "Skylab ".Application::VERSION." (https://github.com/Kunstmaan/skylab)");
        $result = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_status == 403) {
            throw new AccessDeniedException();
        }
        curl_close($ch);
        if ($filename) {
            $this->closeFile($tmpfile);
        } else {
            return $result;
        }

        return false;
    }

    /**
     * @param string|null $filename
     * @param resource    $ch
     *
     * @return bool|resource
     */
    private function setDownloadHeaders($filename, $ch)
    {
        if ($filename) {
            $tempFP = fopen($filename, 'w+');
            curl_setopt($ch, CURLOPT_FILE, $tempFP);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

            return $tempFP;
        }

        return false;
    }

    private function closeFile($tempFP)
    {
        if (!$tempFP) {
            fclose($tempFP);
        }
    }

}
