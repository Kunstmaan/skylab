<?php
namespace Kunstmaan\Skylab\Skeleton;

/**
 * ApacheSkeleton
 */
class PingdomSkeleton extends AbstractSkeleton
{

    const NAME = "pingdom";

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function create(\ArrayObject $project)
    {

    }
    /**
     * @return mixed
     */
    public function preMaintenance()
    {
        // TODO: Implement preMaintenance() method.
    }

    /**
     * @return mixed
     */
    public function postMaintenance()
    {
        // TODO: Implement postMaintenance() method.
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function maintenance(\ArrayObject $project)
    {

        $contactIds = $this->app["config"]["pingdom"]["contactids"];
        $username   = $this->app["config"]["pingdom"]["username"];
        $password   = $this->app["config"]["pingdom"]["password"];
        $token      = $this->app["config"]["pingdom"]["token"];

        if (!(empty($contactIds) && empty($username) && empty($password) && empty($token))){
            $pingdom = new \Pingdom\Client($username, $password, $token);

            $checkId=$pingdom->getCheck($project['name']);
            if (!is_null($checkId)){
                $pingdom->updateHTTPCheck($checkId, $project['name'], $project['url'], "/", "true", "true", "true", $contactIds);
            }else{
                $pingdom->addHTTPCheck($project['name'], $project['url'], "/", "true", "true", "true", $contactIds);
            }
        }
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function preBackup(\ArrayObject $project)
    {
        // TODO: Implement preBackup() method.
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function postBackup(\ArrayObject $project)
    {
        // TODO: Implement postBackup() method.
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function preRemove(\ArrayObject $project)
    {
        // TODO: Implement preRemove() method.
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function postRemove(\ArrayObject $project)
    {
        $username = $this->app["config"]["pingdom"]["username"];
        $password = $this->app["config"]["pingdom"]["password"];
        $token    = $this->app["config"]["pingdom"]["token"];

        $pingdom = new \Pingdom\Client($username, $password, $token);

        foreach ($pingdom->getAllChecks() as $key => $value) {
            if($value['name'] == $project['name']){
                $pingdom->removeCheck($value['id']);
            }
        }
    }

    /**
     * @return string[]
     */
    public function dependsOn()
    {
        return array("base", "apache");
    }

}
