<?php
namespace Kunstmaan\Skylab\Skeleton;

/**
 * StatusCakeSkeleton
 */
class StatusCakeSkeleton extends AbstractSkeleton
{

    const NAME = "statuscake";

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
        if (isset($this->app["config"]["statuscake"]["enabled"]) && $this->app["config"]["statuscake"]["enabled"]){
            $id = $this->getTestId($project["name"]);
            if ($id) {
                $this->updateTest($id, $project);
            } else {
                $this->createTest($project);
            }
        } else {
            $this->dialogProvider->logQuery("Statuscake is disabled", (isset($this->app["config"]["statuscake"])?$this->app["config"]["statuscake"]:$this->app["config"]));
        }
    }

    private function getTestId($name)
    {

        $tests = $this->performAPICall("Tests/", "GET");
        $found = false;
        array_walk($tests, function ($item, $key) use (&$found, $name) {
            if ($item["WebsiteName"] == $name) {
                $found = $item["TestID"];
            }
        });
        return $found;
    }

    private function createTest(\ArrayObject $project)
    {
        $data = $this->getTestArray($project);
        $this->performAPICall("Tests/Update", "PUT", $data);
    }

    private function updateTest($id, \ArrayObject $project)
    {
        $data = $this->getTestArray($project, $id);
        $this->performAPICall("Tests/Update", "PUT", $data);
    }

    private function deleteTest($id)
    {
        $data = $this->getTestArray(null, $id);
        $this->performAPICall("Tests/Details/", "DELETE", $data);
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
                "WebsiteName" => $project["name"] . "." . str_replace(".kunstmaan.com", "", $this->app["config"]["webserver"]["hostmachine"]),
                "Paused" => 0,
                "WebsiteURL" => "http://" . $project["name"] . "." . $this->app["config"]["webserver"]["hostmachine"],
                "CheckRate" => "300",
                "TestType" => "HTTP",
                "WebsiteHost" => $this->app["config"]["webserver"]["hostmachine"],
                "ContactGroup" => 22636,
                "TestTags" => $this->app["config"]["webserver"]["hostmachine"]
            );
        }
        if ($id) {
            $data["TestID"] = $id;
        }
        return $data;
    }

    private function performAPICall($url, $method, $data = null)
    {

        // The data to insert
        $API = $this->app["config"]["statuscake"]["apikey"];
        $Username = $this->app["config"]["statuscake"]["username"];

        // Create the CURL String
        $ch = curl_init("https://www.statuscake.com/API/" . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "API: " . $API,
            "Username: " . $Username
        ));

        $response = curl_exec($ch);
        $response = json_decode($response, true);
        $this->dialogProvider->logConfig("Sent a " . $method . " call to " . "https://www.statuscake.com/API/" . $url . " and got a response " . ($method == "GET"?"with " . sizeof($response) . " checks":$response["Message"]));
        return $response;
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
        if (isset($this->app["config"]["statuscake"]["enabled"]) && $this->app["config"]["statuscake"]["enabled"]) {
            $id = $this->getTestId($project["name"]);
            $this->deleteTest($id);
        } else {
            $this->dialogProvider->logQuery("Statuscake is disabled", (isset($this->app["config"]["statuscake"])?$this->app["config"]["statuscake"]:$this->app["config"]));
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
