<?php
namespace Kunstmaan\Skylab\Entity;

/**
 * ApacheConfig
 */
class ApacheConfig
{

    /**
     * @var string
     */
    private $url;

    /**
     * @var string[]
     */
    private $aliases;

    /**
     * @var string
     */
    private $webDir;

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
    $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
    return $this->url;
    }

    /**
     * @param string[] $aliases
     */
    public function setAliases(array $aliases)
    {
    $this->aliases = $aliases;
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
    return $this->aliases;
    }

    /**
     * @param string $webDir
     */
    public function setWebDir($webDir)
    {
    $this->webDir = $webDir;
    }

    /**
     * @return string
     */
    public function getWebDir()
    {
    return $this->webDir;
    }

}
