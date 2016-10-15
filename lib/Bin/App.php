<?php

namespace lib\Bin;

use lib\Reporter\ReporterInterface;

class App {

    protected $pdo;
    protected $dbs;
    protected $modules;
    
    public function __construct($pdo, $dbs = [])
    {
        $this->pdo = $pdo;
        $this->dbs = $dbs;
    }

    public function run($classModule, $parameters = array()) {
        /** @var ReporterInterface $module */
        $module = new $classModule();
        $module->injectDbs($this->dbs);
        $module->injectPdo($this->pdo);
        $module->injectParameters($parameters);
        return $module->logic();
    }

    // Inject Methods
    public function injectPdo($classPdo)
    {
        $this->pdo = $classPdo;
        return $this;
    }

    public function injectDbs($arrayDbs)
    {
        $this->dbs = $arrayDbs;
        return $this;
    }

    // Retrieve Methods
    public function getPdo()
    {
        return $this->pdo;
    }

    public function getDbs()
    {
        return $this->dbs;
    }

}