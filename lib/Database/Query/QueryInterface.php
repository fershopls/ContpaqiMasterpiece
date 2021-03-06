<?php

namespace lib\Database\Query;

abstract class QueryInterface {

    protected $pdo;
    protected $dbs = array();

    protected $queryFetchMode = \PDO::FETCH_BOTH;
    protected $debugQuery = true;

    abstract public function getQuery();

    abstract public function handle($query_object);

    public function execute($parameters = []) {
        $query = $this->getQuery();

        $result = array();

        if ($this->debugQuery) echo "\n\n[QUERY] [".get_class($this)."]\n[".date("H:i:s")."] {$query}\n";
        $_total = count($this->dbs);
        $_index = 0;
        foreach ($this->dbs as $db)
        {
            $_index++;
            if ($this->debugQuery) echo "\r[".date("H:i:s")."] ".round($_index/$_total *100)."% [{$_index}/$_total] `{$db}`";
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