<?php

namespace SUA\Reporter;

use lib\Reporter\ReporterInterface;

use SUA\Query\DatabaseAvailable;

class DatabaseFinder extends ReporterInterface {

    public function logic ()
    {
        $regs = $this->query(DatabaseAvailable::class)->execute();

        $pdo_dbs = array();
        $info = array();
        foreach ($regs as $dbreg => $rows)
        {
            $rows_total = count($rows);
            $info[$dbreg]['attempts'] = 0;
            $info[$dbreg]['failed'] = 0;

            foreach ($rows as $db_slug => $db_name)
            {
                $info[$dbreg]['attempts']++;
                echo "[{$dbreg}][{$info[$dbreg]['attempts']}/{$rows_total}] Trying to connect to {$db_slug}\t\t\r";

                // Append only successful connections
                if ($db_slug && $this->pdo->testConnection($db_slug))
                    $pdo_dbs[] = $db_slug;
                else
                    $info[$dbreg]['failed']++;
            }
            $info[$dbreg]['success'] = $info[$dbreg]['attempts'] - $info[$dbreg]['failed'];

            echo "\n[{$dbreg}][{$info[$dbreg]['failed']}/{$rows_total}] Attempts Failed.\n";
        }
        // Return existent databases
        return $pdo_dbs;
    }

    public function dump()
    {

    }

}