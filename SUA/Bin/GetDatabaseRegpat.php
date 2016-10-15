<?php

namespace SUA\Bin;

use lib\Reporter\ReporterInterface;
use SUA\Manager\DatabaseManager;
use SUA\Query\DatabaseRegPat;

class GetDatabaseRegpat extends ReporterInterface {

    public function logic ()
    {
        $dbs = $this->import(DatabaseManager::class, ['nomGenerales'])->logic();

        $dbs_regpat = $this->query(DatabaseRegPat::class, $dbs)->execute();

        return $dbs_regpat;
    }

}