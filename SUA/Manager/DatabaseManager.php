<?php

namespace SUA\Manager;

use lib\Reporter\ReporterInterface;

use SUA\Query\DatabaseAvailable;

class DatabaseManager extends ReporterInterface {

    protected $database_strings = array();

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

            $db_slug_char = 0;
            foreach ($rows as $db_slug => $db_name)
            {
                $info[$dbreg]['attempts']++;
                $db_debug = "\r[{$dbreg}][{$info[$dbreg]['attempts']}/{$rows_total}] Connection {$db_slug}";
                for ($i = 0; $i < ($db_slug_char - strlen($db_debug)); $i++)
                    echo " ";
                $db_slug_char = strlen($db_debug);
                echo $db_debug;

                // Append only successful connections
                if ($db_slug && $this->pdo->testConnection($db_slug))
                {
                    $pdo_dbs[] = $db_slug;
                    $this->database_strings[$db_slug] = $db_name;
                } else {
                    $info[$dbreg]['failed']++;
                }
            }
            $info[$dbreg]['success'] = $info[$dbreg]['attempts'] - $info[$dbreg]['failed'];

            echo "\n[{$dbreg}][{$info[$dbreg]['failed']}/{$rows_total}] Attempts Failed.\n";
        }
        // Return existent databases
        return $pdo_dbs;
    }

    public function getStrings ()
    {
        return $this->database_strings;
    }

    public function dump()
    {

    }

}