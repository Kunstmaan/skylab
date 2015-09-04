<?php
namespace Kunstmaan\Skylab\Skeleton;
use UptimeRobot\API;

/**
 * MonitoringSkeleton
 */
class MonitoringSkeleton extends AbstractSkeleton
{

    const NAME = "monitoring";

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
    }

    /**
     * @return mixed
     */
    public function postMaintenance()
    {
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function maintenance(\ArrayObject $project)
    {
        if (isset($this->app["config"]["monitoring"]["enabled"]) && $this->app["config"]["monitoring"]["enabled"]){
            $id = $this->getTestId($project);
            if ($id) {
                $this->updateTest($id, $project);
            } else {
                $this->createTest($project);
            }
        } else {
            $this->dialogProvider->logConfig("Monitoring is disabled");
        }
    }

    private function getCheckName(\ArrayObject $project){
        return $project["name"] . " @ " . str_replace(".kunstmaan.com", "", $this->app["config"]["webserver"]["hostmachine"]);
    }

    private function getTestId(\ArrayObject $project)
    {
        $tests = $this->performAPICall("/getMonitors", array("search" => $this->getCheckName($project)));
        if ($tests["stat"] == "fail"){
            return false;
        } else {
            return $tests["monitors"]["monitor"][0]["id"];
        }
    }

    private function createTest(\ArrayObject $project)
    {
        $data = $this->getTestArray($project);
        $this->performAPICall("/newMonitor", $data);
    }

    private function updateTest($id, \ArrayObject $project)
    {
        $data = $this->getTestArray($project, $id);
        $this->performAPICall("/editMonitor", $data);
    }

    private function deleteTest($id)
    {
        $data = $this->getTestArray(null, $id);
        $this->performAPICall("/deleteMonitor", $data);
    }

    /**
     * @param \ArrayObject $project
     * @return array
     */
    private function getTestArray(\ArrayObject $project = null, $id = false)
    {
        $data = array();
        if (!is_null($project)) {
            $data = array(
                "monitorFriendlyName" => $this->getCheckName($project),
                "monitorURL" => ($this->skeletonProvider->hasSkeleton($project, $this->skeletonProvider->findSkeleton("ssl"))?"https://":"http://") . $project["url"],
                "monitorType" => 1,
                "monitorAlertContacts" => "2351398_5_30-0254691_5_30",
                "monitorInterval" => 1
            );
        }
        if ($id) {
            $data["monitorID"] = $id;
        }
        return $data;
    }

    private function performAPICall($url, $data = array())
    {

        //Set configuration settings
        $config = [
            'apiKey' => $this->app["config"]["monitoring"]["apikey"],
            'url' => 'http://api.uptimerobot.com'
        ];

        try {
            $api = new API($config);
            $results = $api->request($url, $data);
            $this->dialogProvider->logConfig("Sent a call to " . $url . " and got a response: " . print_r($results, true));
            return $results;
        } catch (\Exception $e) {
            $this->dialogProvider->logConfig("Sent a call to " . $url . " and got an error: " . $e->getMessage());
        }
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function preBackup(\ArrayObject $project)
    {
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function postBackup(\ArrayObject $project)
    {
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function preRemove(\ArrayObject $project)
    {
    }

    /**
     * @param \ArrayObject $project
     *
     * @return mixed
     */
    public function postRemove(\ArrayObject $project)
    {
        if (isset($this->app["config"]["monitoring"]["enabled"]) && $this->app["config"]["monitoring"]["enabled"]){
            $id = $this->getTestId($project["name"]);
            $this->deleteTest($id);
        } else {
            $this->dialogProvider->logConfig("Monitoring is disabled");
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
