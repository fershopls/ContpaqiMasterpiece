<?php

namespace lib\Data;

class SettingsManager {

    protected $settings;
    protected $middlewares = array();
    protected $binds = array();

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function has ($index)
    {
        return ($this->get($index, False)===False)?False:True;
    }

    public function get ($indexes, $fallback = [])
    {
        $arrayRoute = explode('.', $indexes);

        $arrayResult = $this->getSettings();
        foreach ($arrayRoute as $index)
        {
            if (is_array($arrayResult) && isset($arrayResult[$index]))
                $arrayResult = $arrayResult[$index];
            else
                $arrayResult = false;
        }

        $arrayResult = ($arrayResult)?$arrayResult:$fallback;
        return $this->runBinds($indexes, $arrayResult);
    }

    public function middleware ($id, $callback = null)
    {
        if ($callback === null)
            return isset($this->middlewares[$id])?$this->middlewares[$id]:null;
        else
            $this->middlewares[$id] = $callback;
        return $this;
    }

    public function bind ($regex, $middleware)
    {
        $this->binds[] = ['regex'=>$regex, 'middleware'=>$middleware];
        return $this;
    }

    public function runBinds ($indexes, $property)
    {
        foreach ($this->binds as $bind)
        {
            if (preg_match($bind['regex'], $indexes))
            {
                if (is_callable($bind['middleware']))
                    $property = call_user_func_array($bind['middleware'], [$property]);
                elseif (is_string($bind['middleware']) && $this->middleware($bind['middleware']))
                    $property = call_user_func_array($this->middleware($bind['middleware']), [$property]);
            }
        }
        return $property;
    }

}
