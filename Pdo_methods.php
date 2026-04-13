<?php
require_once 'Db_conn.php';

class Pdo_methods
{

    // Insert a record into a table
    // $table: table name (string)
    // $data: associative array of column => value
    public static function insert($table, $data)
    {
        $conn = Db_conn::getConnection();

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($data);

        return $conn->lastInsertId();
    }

    // Select all records from a table
    // $table: table name (string)
    // Returns: array of associative arrays
    public static function selectAll($table)
    {
        $conn = Db_conn::getConnection();

        $sql = "SELECT * FROM $table";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Select records where a column matches a value
    // $table: table name (string)
    // $column: column name (string)
    // $value: value to match
    // Returns: array of associative arrays
    public static function selectWhere($table, $column, $value)
    {
        $conn = Db_conn::getConnection();

        $sql = "SELECT * FROM $table WHERE $column = :value";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':value' => $value]);

        return $stmt->fetchAll();
    }
}
