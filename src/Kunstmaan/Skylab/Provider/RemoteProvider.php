<?php


namespace Kunstmaan\Skylab\Provider;


use Cilex\Application;

class RemoteProvider extends AbstractProvider
{

    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['remote'] = $this;
        $this->app = $app;
    }


    /**
     * @param $url
     * @param  string $contentType
     * @param  string $filename
     * @return mixed
     */
    public function curl($url, $contentType = null, $filename = null)
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
        $tmpfile = $this->setDownloadHeaders($filename, $ch);
        curl_setopt($ch, CURLOPT_USERAGENT, "Skylab " . Application::VERSION . " (https://github.com/Kunstmaan/skylab)");
        $result = curl_exec($ch);
        curl_close($ch);
        if ($filename) {
            $this->closeFile($tmpfile);
        } else {
            return $result;
        }
        return false;
    }

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
