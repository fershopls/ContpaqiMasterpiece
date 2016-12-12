<?php

namespace LIR\Bin;

use lib\Reporter\ReporterInterface;

// Managers
use SUA\Manager\DatabaseManager;
// Query
use SUA\Query\DatabaseRegPat;

class GetAvailableDatabases extends ReporterInterface {

    public function logic ()
    {
        // Inject available dbs
        $databaseManager = $this->import(DatabaseManager::class, ['nomGenerales'], $this->pdo);

        $dbs = $databaseManager->logic();
        $this->injectDbs($dbs);

        $regpat = isset($this->parameters['regpat'])?$this->parameters['regpat']:null;
        if ($regpat)
        {
            // Get databases regpat
            $dbs_regpat = $this->query(DatabaseRegPat::class)->execute();
            // Filter databases by regpat
            $dbs_filter = array_filter($dbs_regpat, function($e) use ($regpat) {
                foreach ($e as $rp)
                {
                    if ($rp == $regpat)
                        return true;
                }
                return false;
            });
            // Get results
            $dbs = array_keys($dbs_filter);
        }

        return ["dbs" => $dbs, "strings"=>$databaseManager->getStrings()];
    }

}