<?php

namespace lib\Data;

class SettingsManager {

    protected $settings;
    protected $binds;

    public function __construct($arraySettings)
    {
        $this->settings = $arraySettings;
        $this->binds = array();
    }

    public function getSettings ()
    {
        return $this->settings;
    }

    public function get ($indexes, $fallback = [])
    {
        $arrayRoute = explode('.', $indexes);

        $arrayResult = $this->getSettings();
        foreach ($arrayRoute as $index)
        {
            if (is_array($arrayResult) && isset($arrayResult[$index]))
            {
                $arrayResult = $arrayResult[$index];
            } else {
                $arrayResult = false;
                continue;
            }
        }

        $arrayResult = ($arrayResult)?$arrayResult:$fallback;
        return $this->runBinds($indexes, $arrayResult);
    }

    public function bind ($regex, $callback)
    {
        $this->binds[] = ['regex'=>$regex, 'callback'=>$callback];
    }

    public function runBinds($regex, $property)
    {
        foreach ($this->binds as $bind)
        {
            if (preg_match($bind['regex'], $regex))
            {
                $property = $bind['callback']($property);
            }
        }
        return $property;
    }

}