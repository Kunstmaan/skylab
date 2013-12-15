<?php
namespace Kunstmaan\Skylab\Entity;

/**
 * PostgreSQLConfig
 */
class PostgreSQLConfig
{

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @param string $host
     */
    public function setHost($host)
    {
	$this->host = $host;
    }

    /**
     * @return string
     */
    public function getHost()
    {
	return $this->host;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
	$this->password = $password;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
	return $this->password;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
	$this->port = $port;
    }

    /**
     * @return int
     */
    public function getPort()
    {
	return $this->port;
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
	$this->user = $user;
    }

    /**
     * @return string
     */
    public function getUser()
    {
	return $this->user;
    }

}