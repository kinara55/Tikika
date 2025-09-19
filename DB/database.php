<?php

class Database {
    private $connection;
    private $conf;
    
    public function __construct($conf) {
        $this->conf = $conf;
        $this->connect();
    }
    private function connect() {
        $this->connection = new mysqli(
            $this->conf['DB_HOST'],
            $this->conf['DB_USER'],
            $this->conf['DB_PASS'],
            $this->conf['DB_NAME']
        );
        
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
        
        $this->connection->set_charset("utf8mb4");
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->connection->error);
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        
        if ($stmt->error) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        return $stmt;
    }
    
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->query($sql, array_values($data));
        
        return $this->connection->insert_id;
    }
    
    public function update($table, $data, $where, $where_params = []) {
        $set_clause = implode('=?,', array_keys($data)) . '=?';
        $sql = "UPDATE $table SET $set_clause WHERE $where";
        
        $params = array_merge(array_values($data), $where_params);
        $stmt = $this->query($sql, $params);
        
        return $stmt->affected_rows;
    }
    
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->query($sql, $params);
        
        return $stmt->affected_rows;
    }
    
    public function beginTransaction() {
        $this->connection->begin_transaction();
    }
    
    public function commit() {
        $this->connection->commit();
    }
    
    public function rollback() {
        $this->connection->rollback();
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
