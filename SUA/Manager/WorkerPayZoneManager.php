<?php

namespace SUA\Manager;

use lib\Reporter\ReporterInterface;

use SUA\Query\WorkerZone;
use SUA\Query\WorkerPay;

class WorkerPayZoneManager extends ReporterInterface {

    protected $cache = array();

    public function get ($db_slug, $db_zone)
    {
        if (isset($this->cache[$db_slug][$db_zone])) {
            $pay = $this->cache[$db_slug][$db_zone];
        } else {
            $zone = $this->query(WorkerZone::class, [$db_slug])->execute([
                'zone' => 'Zona_' . strtoupper($db_zone)
            ])[$db_slug];
            $pay = $this->query(WorkerPay::class, [$db_slug])->execute([
                'numerotabla' => $zone['numerotabla'],
                'numerocolumna' => $zone['numerocolumna'],
            ])[$db_slug]['valor'];

            $this->cache[$db_slug][$db_zone] = $pay;
        }

        return $pay;
    }

}