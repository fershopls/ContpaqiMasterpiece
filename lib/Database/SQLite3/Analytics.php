<?php

namespace lib\Database\SQLite3;

use SQLite3;

class Analytics {

    CONST TABLE_NAME = 'ANALYTICS';

    protected $database;

    public function __construct($SQLiteDatabasePath)
    {
        $this->database = new SQLite3($SQLiteDatabasePath . DIRECTORY_SEPARATOR . 'analytics.db');
        $this->createTable();
    }

    public function db()
    {
        return $this->database;
    }

    public function getTableSQL ()
    {
        return "CREATE TABLE IF NOT EXISTS ". self::TABLE_NAME ."(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            frontend_id VARCHAR(200),
            params_frontend VARCHAR(400),
            params_backend VARCHAR(400),
            created_at DATETIME,
            started_at DATETIME,
            ended_at DATETIME,
            status_start VARCHAR(600),
            status_end VARCHAR(600)
        );";
    }

    public function getInsertSQL ()
    {
        return 'INSERT INTO '. self::TABLE_NAME .' (frontend_id, params_frontend, params_backend, created_at, started_at, ended_at, status_start, status_end) VALUES (:frontend_id, :params_frontend, :params_backend, :created_at, :started_at, :ended_at, :status_start, :status_end);';
    }

    public function getUpdateSQL ()
    {
        return 'UPDATE '.self::TABLE_NAME.' SET 
        frontend_id = :frontend_id, 
        params_frontend = :params_frontend, 
        params_backend = :params_backend, 
        created_at = :created_at, 
        started_at = :started_at, 
        ended_at = :ended_at,
        status_start = :status_start,
        status_end = :status_end
        WHERE `id` = :id;';
    }

    public function getDefaultObject ($object = null)
    {
        if ($object && is_array($object))
            return array_merge($this->getDefaultObject(), $object);
        else
            return array(
                'frontend_id' => '0',
                'params_frontend' => '0',
                'params_backend' => '0',
                'created_at' => '0',
                'started_at' => '0',
                'ended_at' => '0',
                'status_start' => '0',
                'status_end' => '0',
            );
    }

    public function createTable ()
    {
        return $this->db()->query($this->getTableSQL());
    }

    public function create($object)
    {
        $object = $this->getDefaultObject($object);
        $q = $this->db()->prepare($this->getInsertSQL());
        foreach ($object as $key => $value)
            $q->bindValue($key, $value);
        $q->execute();
        $object['id'] = $this->db()->lastInsertRowID();
        return $object;
    }

    public function query ($query)
    {
        $results = $this->db()->query($query);
        return $this->fetchAll($results);
    }

    public function fetchAll($results)
    {
        $data = array();
        while ($row = $results->fetchArray(SQLITE3_ASSOC))
            array_push($data, $row);
        return $data;
    }

    public function get ($id)
    {
        $q = $this->db()->prepare('SELECT * FROM '.self::TABLE_NAME.' WHERE `id` = :id');
        $q->bindValue('id', $id);
        $result = $this->fetchAll($q->execute());
        return count($result)==0?null:$result[0];
    }
    
    public function update ($id, $object)
    {
        if ($this->get($id) == null)
            return False;
        $object = $this->getDefaultObject($object);
        $q = $this->db()->prepare($this->getUpdateSQL());
        foreach ($object as $key => $value)
            $q->bindValue($key, $value);
        $q->execute();
        return $object;
    }

}