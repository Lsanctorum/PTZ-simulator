<?php

namespace PtzSimulator\Manager;

use PDO;

/**
 * Class EntityManager
 * @package PtzSimulator\Manager
 */
class EntityManager
{
    /**
     * @var PDO
     */
    private $connection;

    /**
     * EntityManager constructor.
     */
    public function __construct()
    {
        try {
            $this->connection = new PDO('sqlite:' . __DIR__ . '/../../data/db/ptz-simulator.db');
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\Throwable $e) {
            var_dump($e); die;
        }

        $this->connection->exec(
            'CREATE TABLE IF NOT EXISTS cities 
            (id TEXT PRIMARY KEY, name TEXT, zone TEXT, department TEXT, zip_codes TEXT, 
            latitude REAL, longitude REAL, cos_lat REAL, sin_lat REAL, cos_lng REAL, sin_lng REAL)'
        );
    }

    /**
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }
}