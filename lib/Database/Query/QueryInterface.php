<?php

namespace lib\Database\Query;

abstract class QueryInterface {

    protected $pdo;
    protected $dbs = array();

    protected $queryFetchMode = \PDO::FETCH_BOTH;

    abstract public function getQuery();

    abstract public function handle($query_object);

    public function execute($parameters = []) {
        $query = $this->getQuery();

        $result = array();

        foreach ($this->dbs as $db)
        {
            $q = $this->pdo->using($db)->prepare($query);
            $q->setFetchMode($this->queryFetchMode);
            $q->execute($parameters);
            $result[$db] = $q->fetchAll();
        }

        return $this->handle($result);
    }

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
}