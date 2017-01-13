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

class LIR extends ReporterInterface {

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
                continue;

            if (count($period_type) && $this->parameters['period_type'] != '')
                $params['period_type'] = array_values($period_type)[0]['idtipoperiodo'];

            foreach ($db_workers as $dbw_row)
            {
                // Skip worker if has `bajaimms  & `worker_down` option is not active
                if ($this->parameters['options']['worker_down'] == false && $dbw_row['bajaimss'] == 1)
                    continue;
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

        // Sheet commands
        $customReport = new \lib\Data\Sheet();

        $fields = array(
          'factura' => 'Factura',
          'empresa' => 'Empresa',
          'empleado_codigo' => 'Codigo de Empleado',
          'empleado_nombre' => 'Nombre de Empleado',
          'pago_tipo' => 'Forma de Pago',
          'estatus' => 'Estatus',
          'estatus_fecha' => 'Fecha Estatus',
          'periodo_tipo' => 'Tipo de Periodo',
          'periodo_num' => 'No. de Periodo',
          'periodo_fecha' => 'Fecha de Periodo',
        );
        // Fill sources & headers
        foreach ($fields as $id => $header)
          $customReport->insertSource($id, $header);
        // Custom fields
        $customReport->insertCustomField('periodo_fecha', function($row){
          return $row['periodo_tipo'].' del '.date("d/m/Y", strtotime($row['periodo_fecha_inicio'])).' al '.date("d/m/Y", strtotime($row['periodo_fecha_fin']));
        });


        foreach ($db_worker_concept_dic as $db_slug => $_db_period)
        {
            foreach ($_db_period as $period_id => $_db_worker)
            {
                foreach ($_db_worker as $worker_id => $_db_concept)
                {
                    $_period_type_id = $db_period_dic[$db_slug][$period_id]['idtipoperiodo'];
                    $_period_type_key = StringKey::get($db_period_type_dic[$db_slug][$_period_type_id]['nombretipoperiodo']);

                    // Set invoice
                    $db_worker_dic[$db_slug][$worker_id]['invoice'] =
                        ($db_worker_dic[$db_slug][$worker_id]['bajaimss'] == 1
                            && $db_worker_dic[$db_slug][$worker_id]['fechabaja'] >= $db_period_dic[$db_slug][$period_id]['fechainicio']
                            && $db_worker_dic[$db_slug][$worker_id]['fechabaja'] <= $db_period_dic[$db_slug][$period_id]['fechafin'])?$db_worker_dic[$db_slug][$worker_id]['campoextra1']:'';

                    $row['factura'] = $db_worker_dic[$db_slug][$worker_id]['invoice'];
                    $row['empresa'] = isset($dbs_strings[$db_slug])?$dbs_strings[$db_slug]:$db_slug;;
                    $row['empleado_codigo'] = ' '.$db_worker_dic[$db_slug][$worker_id]['codigoempleado'];
                    $row['empleado_nombre'] = $db_worker_dic[$db_slug][$worker_id]['nombrelargo'];
                    $row['pago_tipo'] = $db_worker_dic[$db_slug][$worker_id]['payment_type'];
                    $row['estatus'] = isset($db_worker_status_dic[$db_slug][$period_id][$worker_id][$db_slug]['status'])?$db_worker_status_dic[$db_slug][$period_id][$worker_id][$db_slug]['status']:DbWorkerStatusDic::DEFAULT_STATUS;
                    $row['estatus_fecha'] = isset($db_worker_status_dic[$db_slug][$period_id][$worker_id][$db_slug]['status_date'])?$db_worker_status_dic[$db_slug][$period_id][$worker_id][$db_slug]['status_date']:'';
                    $row['periodo_tipo'] = ucfirst($_period_type_key);
                    $row['periodo_num'] = $db_period_dic[$db_slug][$period_id]['numeroperiodo'];
                    $row['periodo_fecha_inicio'] = $db_period_dic[$db_slug][$period_id]['fechainicio'];
                    $row['periodo_fecha_fin'] = $db_period_dic[$db_slug][$period_id]['fechafin'];

                    $_concept_type_last = null;
                    $_concept_type_total = 0;
                    $db_concept_ordered['FINAL'] = [];
                    foreach ($db_concept_ordered  as $_concept_type => $_concept_group)
                    {
                        if ($_concept_type_last && $_concept_type_last != $_concept_type)
                        {
                            if ($_concept_type_last != 'N')
                            {
                                $concept_txt = "Total ".$concept_type_string[$_concept_type_last];
                                $concept_row = $dh->getConceptId($concept_txt);
                                // Insert source
                                $customReport->insertSource($concept_row, $concept_txt);
                                // attach to row
                                $row[$concept_row] = $_concept_type_total;
                            }
                            $_concept_type_total = 0;
                        }
                        $_concept_type_last = $_concept_type;
                        foreach ($_concept_group as $_concept_key => $i)
                        {
                            $concept_value = isset($_db_concept[$_concept_key])?$_db_concept[$_concept_key]:0;
                            $concept_row = $dh->getConceptId($db_key_concept_dic[$_concept_key]['descripcion']);
                            // Insert source
                            $customReport->insertSource($concept_row, $db_key_concept_dic[$_concept_key]['descripcion']);
                            // attach to row
                            $row[$concept_row] = $concept_value;
                            $_concept_type_total += $concept_value;
                        }
                    }

                    // Insert rows
                    $customReport->insertRow($row);
                }
            }
        }

        // Get a nice report
        file_put_contents($this->parameters['filename'], $customReport->csv($customReport->get()));

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
