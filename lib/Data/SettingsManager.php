<?php

namespace lib\Data;

class SettingsManager {

    protected $settings;

    public function __construct($arraySettings)
    {
        $this->settings = $arraySettings;
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
        return ($arrayResult)?$arrayResult:$fallback;
    }

}