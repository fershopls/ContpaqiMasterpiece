<?php

namespace LIR\Bin;

use lib\Reporter\ReporterInterface;

// Managers
use LIR\Query\DbWorkerStatusDic;

// Query
use LIR\Query\DbWorkerDic;
use LIR\Query\DbPeriodDic;
use LIR\Query\DbPeriodTypeDic;
use LIR\Query\DbConceptDic;
use LIR\Query\DbKeyConceptDic;

// Util
use LIR\Util\StringKey;
use LIR\Util\DataHandler;
use lib\Database\SQLite3\Analytics;

class LIR extends ReporterInterface {
    
    public function status ($msg, $save = true)
    {
        echo "\n[STATUS]: {$msg}\n";
        if (!$save) return true;
        if (!isset($this->parameters['id']) || !isset($this->parameters['__cache_dir']))
        {
            echo "[ANALYTICS_ERROR] Parameter id or __cache_dir missing:\n\n\n";
            return false;
        }
        $ana = isset($this->ana)?$this->ana:new Analytics($this->parameters['__cache_dir']);
        $obj = $ana->get($this->parameters['id']);
        if ($obj)
        {
            $obj['status_end'] .= $msg.'. ';
            $res = $ana->update($obj['id'], $obj);
        } else {
            echo "[ANALYTICS_ERROR] Id not found on database.\n\n";
        }
        return $obj;
    }

    public function logic ()
    {
        $dbs = $this->getAvailableDatabases($this->parameters);
        $dbs_strings = $dbs['strings'];
        $dbs = $dbs['dbs'];
        $this->injectDbs($dbs);

        // Dictionaries
        // Regpat & Key Concept skipped
        $db_worker_dic = $this->query(DbWorkerDic::class)
            ->execute();

        $db_period_dic = $this->query(DbPeriodDic::class)
            ->execute();

        $db_period_type_dic = $this->query(DbPeriodTypeDic::class)
            ->execute();

        $db_concept_dic = $this->query(DbConceptDic::class)
            ->execute();

        $db_key_concept_dic = $this->query(DbKeyConceptDic::class)
            ->execute();

        // Solve Relationships
        $db_worker_status_dic = array();
        $db_worker_concept_dic = array();

        // Debug
        $this->status("Query DB Worker DIC returned ".count($db_worker_dic)." results");

        foreach ($db_worker_dic as $db_slug => $db_workers)
        {
            // Get period type
            $period_type = StringKey::get($this->parameters['period_type']);
            $period_type = array_filter($db_period_type_dic[$db_slug], function ($ob) use ($period_type) {
                return StringKey::get($ob['nombretipoperiodo']) == $period_type;
            });

            // Set search parameters
            $params = [
                'worker_id' => null, // We'll define it later
                'date_begin' => $this->parameters['date_begin'],
                'date_end'   => $this->parameters['date_end'],
                'exercise'   => $this->parameters['exercise'],
            ];

            // Skip database if don't have `period_type` desired
            if (count($period_type) == 0 && $this->parameters['period_type']!='')
            {
                // Debug
                $this->status("Database {$db_slug} skipped because don't have period_type desired.");

                continue;
            }

            if (count($period_type) && $this->parameters['period_type'] != '')
                $params['period_type'] = array_values($period_type)[0]['idtipoperiodo'];

            // Debug
            $this->status("DB Workers from {$db_slug} returned ".count($db_workers)." results");

            foreach ($db_workers as $dbw_row)
            {
                // Skip worker if has `bajaimms  & `worker_down` option is not active
                if ($this->parameters['options']['worker_down'] == false && $dbw_row['bajaimss'] == 1)
                {
                    // Debug
                    $this->status("Worker ".json_encode($dbw_row)." skipped: bajaimss = 1;");

                    continue;
                }

                // Set search parameter worker_id
                $params['worker_id'] = $dbw_row['idempleado'];

                // If `period_type` = '' find any period type
                $q = $this->pdo->using($db_slug)
                    ->prepare("SELECT mv.idconcepto, mv.idperiodo, mv.importetotal FROM [nom10007] mv, [nom10002] pr WHERE mv.idempleado = :worker_id AND pr.idperiodo = mv.idperiodo AND pr.fechainicio BETWEEN :date_begin AND :date_end AND pr.ejercicio = :exercise AND mv.importetotal != 0". (!isset($params['period_type'])?'':" AND pr.idtipoperiodo = :period_type"));
                $q->execute($params);

                $wmv_rows = $q->fetchAll();
                foreach ($wmv_rows as $wmv_row)
                {
                    // Worker Movements
                    $_cpt_key = StringKey::get($db_concept_dic[$db_slug][$wmv_row['idconcepto']]['descripcion']);
                    $_cpt_type = $db_key_concept_dic[$_cpt_key]['tipoconcepto'];

                    $db_concept_ordered [$_cpt_type][$_cpt_key] = 1;

                    $db_worker_concept_dic [$db_slug] [$wmv_row['idperiodo']] [$dbw_row['idempleado']] [$_cpt_key] = $wmv_row['importetotal'];

                    // Worker Status
                    if (!isset($db_worker_status_dic[$db_slug][$wmv_row['idperiodo']][$dbw_row['idempleado']]))
                    {
                        // Make sure every row had a status
                        $db_worker_status_dic[$db_slug][$wmv_row['idperiodo']][$dbw_row['idempleado']] = $this->query(DbWorkerStatusDic::class)
                            ->execute(array('id_periodo'=> $wmv_row['idperiodo'], 'id_empleado'=> $dbw_row['idempleado']));
                    }
                }

            }
        }

        //
        // DUMP
        //
        
        $dh = new DataHandler();

        $concept_type_string = [
            "D" => 'Deducciones',
            "P" => 'Percepciones',
            "O" => 'Obligaciones',
        ];

        $csv_rows = [];


        // Debug
        $this->status("ON DUMP: db worker concept dic has ".count($db_worker_concept_dic)." rows");

        foreach ($db_worker_concept_dic as $db_slug => $_db_period)
        {
            // Debug
            $this->status("ON DUMP: {$db_slug} periods array has ".count($_db_period)." rows", false);

            foreach ($_db_period as $period_id => $_db_worker)
            {
                foreach ($_db_worker as $worker_id => $_db_concept)
                {
                    $_period_type_id = $db_period_dic[$db_slug][$period_id]['idtipoperiodo'];
                    $_period_type_key = StringKey::get($db_period_type_dic[$db_slug][$_period_type_id]['nombretipoperiodo']);

                    $csv_id = md5($db_slug).$period_id.$worker_id;

                    // Set invoice
                    $db_worker_dic[$db_slug][$worker_id]['invoice'] =
                        ($db_worker_dic[$db_slug][$worker_id]['bajaimss'] == 1
                            && $db_worker_dic[$db_slug][$worker_id]['fechabaja'] >= $db_period_dic[$db_slug][$period_id]['fechainicio']
                            && $db_worker_dic[$db_slug][$worker_id]['fechabaja'] <= $db_period_dic[$db_slug][$period_id]['fechafin'])?$db_worker_dic[$db_slug][$worker_id]['campoextra1']:'';

                    $db_name = isset($dbs_strings[$db_slug])?$dbs_strings[$db_slug]:$db_slug;
                    $csv_rows[$csv_id][$dh->getConceptId('Factura')] = $db_worker_dic[$db_slug][$worker_id]['invoice'];
                    $csv_rows[$csv_id][$dh->getConceptId('Empresa')] = $db_name;
                    $csv_rows[$csv_id][$dh->getConceptId('Codigo de Empleado')] = $db_worker_dic[$db_slug][$worker_id]['codigoempleado'];
                    $csv_rows[$csv_id][$dh->getConceptId('Nombre de Empleado')] = $db_worker_dic[$db_slug][$worker_id]['nombrelargo'];
                    $csv_rows[$csv_id][$dh->getConceptId('Forma de Pago')] = $db_worker_dic[$db_slug][$worker_id]['payment_type'];
                    $csv_rows[$csv_id][$dh->getConceptId('Estatus')] = isset($db_worker_status_dic[$db_slug][$period_id][$worker_id][$db_slug]['status'])?$db_worker_status_dic[$db_slug][$period_id][$worker_id][$db_slug]['status']:DbWorkerStatusDic::DEFAULT_STATUS;
                    $csv_rows[$csv_id][$dh->getConceptId('Fecha Estatus')] = isset($db_worker_status_dic[$db_slug][$period_id][$worker_id][$db_slug]['status_date'])?$db_worker_status_dic[$db_slug][$period_id][$worker_id][$db_slug]['status_date']:'';
                    $csv_rows[$csv_id][$dh->getConceptId('Tipo de Periodo')] = $_period_type_key;
                    $csv_rows[$csv_id][$dh->getConceptId('No. de Periodo')] = $db_period_dic[$db_slug][$period_id]['numeroperiodo'];
                    # $csv_rows[$csv_id][$dh->getConceptId('Fecha Inicio')] = $db_period_dic[$db_slug][$period_id]['fechainicio'];
                    # $csv_rows[$csv_id][$dh->getConceptId('Fecha Fin')] = $db_period_dic[$db_slug][$period_id]['fechafin'];
                    $csv_rows[$csv_id][$dh->getConceptId('Fecha Periodo')] = ucfirst($_period_type_key).' del '.date("d/m/Y", strtotime($db_period_dic[$db_slug][$period_id]['fechainicio'])).' al '.date("d/m/Y", strtotime($db_period_dic[$db_slug][$period_id]['fechafin']));

                    $_concept_type_last = null;
                    $_concept_type_total = 0;
                    $db_concept_ordered['FINAL'] = [];

                    // Debug
                    $this->status("ON DUMP ORDERING: db_concept_ordered array has ".count($db_concept_ordered)." rows", false);

                    foreach ($db_concept_ordered  as $_concept_type => $_concept_group)
                    {
                        if ($_concept_type_last && $_concept_type_last != $_concept_type)
                        {
                            if ($_concept_type_last != 'N')
                            {
                                $concept_row = $dh->getConceptId("Total ".$concept_type_string[$_concept_type_last]);
                                $csv_rows[$csv_id][$concept_row] = $_concept_type_total;
                            }
                            $_concept_type_total = 0;
                        }
                        $_concept_type_last = $_concept_type;

                        // Debug
                        $this->status("ON DUMP ORDERING: _concept_group array has ".count($_concept_group)." rows", false);

                        foreach ($_concept_group as $_concept_key => $i)
                        {
                            $concept_value = isset($_db_concept[$_concept_key])?$_db_concept[$_concept_key]:0;
                            $concept_row = $dh->getConceptId($db_key_concept_dic[$_concept_key]['descripcion']);
                            $csv_rows[$csv_id][$concept_row] = $concept_value;
                            $_concept_type_total += $concept_value;
                        }
                    }

                }
            }
        }

        file_put_contents($this->parameters['filename'], $this->createCsv($dh->getHeaders(), $csv_rows));

        // Debug
        if (!file_exists($this->parameters['filename'])) $this->status("ON WRITING FILE: Error on file_put_contents.");

    }

    public function getAvailableDatabases ($parameters)
    {
        $dbs = $this->import(GetAvailableDatabases::class, null, null, array('regpat'=>$parameters['regpat']))->logic();
        if ($parameters['database'] != '')
        {
            $dbs_filtered = array();
            $dbs_str_filtered = array();
            foreach ($dbs['strings'] as $db_slug => $db_string)
            {
                if ($db_slug == $parameters['database'])
                {
                    $dbs_filtered[$db_slug] = $db_slug;
                    $dbs_str_filtered[$db_slug] = $db_string;
                }
            }
            return array('dbs' => $dbs_filtered, 'strings' => $dbs_str_filtered);
        }
        return $dbs;
    }

}