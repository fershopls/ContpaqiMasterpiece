<?php

namespace lib\Reporter;

use lib\Database\Query\QueryInterface;

use lib\Data\CSV;

class ReporterInterface {

    protected $pdo;
    protected $dbs;
    protected $parameters = array();

    protected $imports = array();
    protected $querys = array();

    // Import & inject ObjectClass to use in main ObjectClass
    public function import ($classObject, $databases = null, $pdo = null, $parameters = null)
    {
        /* @var ReporterInterface $object */
        $object = isset($this->imports[$classObject])?$this->imports[$classObject]:new $classObject;

        $pdo = $pdo===null?$this->pdo:$pdo;
        $object->injectPdo($pdo);

        $databases = $databases===null?$this->dbs:$databases;
        $object->injectDbs($databases);

        $parameters = $parameters===null?$this->parameters:$parameters;
        $object->injectParameters($parameters);

        return $object;
    }

    // Create & inject QueryClass given to use in ObjectClass
    public function query ($classQuery, $databases = null, $pdo = null)
    {
        /* @var QueryInterface $query */
        $query = isset($this->imports[$classQuery])?$this->imports[$classQuery]:new $classQuery;

        $pdo = $pdo===null?$this->pdo:$pdo;
        $query->injectPdo($pdo);

        $dbs = $databases===null?$this->dbs:$databases;
        $query->injectDbs($dbs);

        return $query;
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

    public function injectParameters($arrayParameters)
    {
        $this->parameters = $arrayParameters;
        return $this;
    }

    // Create CSV-type string using $csv_headers as reference for $csv_rows
    public function createCsv($csv_headers, $csv_rows, $csv_fix_top = [])
    {
        $csv = new CSV();

        $csv->writerow($csv_fix_top);
        $csv->writerow($csv_headers);

        foreach ($csv_rows as $csv_row)
        {
            $row = array();
            // Only append $row values if they are into $csv_headers.keys()
            foreach ($csv_headers as $hid => $htext)
            {
                $row[$hid] = isset($csv_row[$hid])?$csv_row[$hid]:'';
            }
            $csv->writerow($row);
        }

        return $csv->get();
    }

}