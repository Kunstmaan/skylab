<?php


namespace Kunstmaan\Skylab\Utility;


class DependencySolver
{

    private $items = array();
    private $dependencies = array();

    /**
     * @param $item
     * @param array $dependencies
     */
    public function add($item, $dependencies = array())
    {
        $this->items[$item] = (count($dependencies) > 0) ? $dependencies : null;

        foreach ($dependencies as $dependency) {
            $this->dependencies[$dependency][] = $item;
        }
    }

    /**
     * @return array
     */
    public function getLoadOrder()
    {
        $loadOrder = array();
        $seen = array();

        foreach ($this->items as $item => $dependencies) {
            $tmp = $this->getDependents($item, $seen);

            if ($tmp[2] === false) {
                $loadOrder = array_merge($loadOrder, $tmp[0]);
                $seen = $tmp[1];
            }
        }

        $loadOrder = array_filter($loadOrder, function($skeleton){
            return $skeleton != "base";
        });
        array_unshift($loadOrder, "base");

        return $loadOrder;
    }

    /**
     * @param $item
     * @param array $seen
     * @return array
     */
    private function getDependents($item, $seen = array())
    {
        if (array_key_exists($item, $seen)) {
            return array(array(), $seen, false);
        }

        if ($this->itemExists($item)) {
            $order = array();
            $failed = array();
            $seen[$item] = true;

            if ($this->hasDependents($item)) {
                foreach ($this->items[$item] as $dependency) {
                    $tmp = $this->getDependents($dependency, $seen);

                    $order = array_merge($tmp[0], $order);
                    $seen = $tmp[1];

                    if ($tmp[2] !== false) {
                        $failed = array_merge($tmp[2], $failed);
                    }
                }
            }

            $order[] = $item;
            $failed = (count($failed) > 0) ? $failed : false;

            return array($order, $seen, $failed);
        }

        return array(array(), array(), array($item));
    }

    /**
     * @param $item
     * @return bool
     */
    public function itemExists($item)
    {
        if (array_key_exists($item, $this->items)) {
            return true;
        }

        return false;
    }

    /**
     * @param $item
     * @return bool
     */
    private function hasDependents($item)
    {
        if ($this->itemExists($item) AND is_array($this->items[$item])) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getFailedItems()
    {
        $failed = array();
        $seen = array();

        foreach ($this->items as $item => $dependencies) {
            $tmp = $this->getDependents($item, $seen);

            if ($tmp[2] !== false) {
                $failed[] = $item;
                continue;
            }

            $seen = $tmp[1];
        }

        return $failed;
    }
}
