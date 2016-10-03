<?php

namespace lib\Database;

use PDO;

class StackPDO {
    protected $credentials;
    protected $stack = array();

    public function __construct($hosting, $username, $password)
    {
        $this->setCredentials($hosting, $username, $password);
    }

    public function using ($database, $stackable = true)
    {
        $pdo = isset($this->stack[$database])?$this->stack[$database]:false;

        if (!$pdo)
        {
            $pdo = $this->createConnection($database);
            if ($pdo && $stackable)
                $this->stack[$database] = $pdo;
        }

        return $pdo;
    }

    public function createConnection ($database_slug, $credentials = null)
    {
        $auth = $credentials===null?$this->credentials:$credentials;
        return new PDO("sqlsrv:Server=".$auth['hosting'].";Database=".$database_slug, $auth['username'], $auth['password']);
    }

    public function testConnection ($database_slug)
    {
        try {
            return $this->using($database_slug);
        } catch(\PDOException $e) {
            return False;
        }
    }

    public function setCredentials ($hosting, $username, $password)
    {
        $this->credentials = array(
            'hosting' => $hosting,
            'username' => $username,
            'password' => $password,
        );
    }
}