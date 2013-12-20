<?php
namespace Kunstmaan\Skylab\Entity;

/**
 * PermissionDefinition
 */
class PermissionDefinition
{

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $ownership;

    /**
     * @var string[]
     */
    private $acl = array();

    /**
     * @param string $acl
     */
    public function addAcl($acl)
    {
        $this->acl[] = $acl;
    }

    /**
     * @return string[]
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * @param string $ownership
     */
    public function setOwnership($ownership)
    {
        $this->ownership = $ownership;
    }

    /**
     * @return string
     */
    public function getOwnership()
    {
        return $this->ownership;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

}
