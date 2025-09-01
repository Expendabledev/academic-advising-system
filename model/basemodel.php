<?php
// models/basemodel.php
require_once __DIR__ . '/../config/database.php';

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct($db = null) {
        if ($db instanceof PDO) {
            $this->db = $db;
        } else {
            $this->db = $this->getDatabase();
        }
    }
    
    private function getDatabase() {
        $host = 'localhost';
        $dbname = 'academic_advising';
        $username = 'root';
        $password = '';
        
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    public function getAll($orderBy = 'created_at', $order = 'DESC') {
        try {
            if (!$this->table) {
                throw new Exception("Table name not defined");
            }
            
            $query = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$order}";
            return $this->executeQuery($query)->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getAll() for table {$this->table}: " . $e->getMessage());
            return [];
        }
    }
    
    public function getById($id) {
        try {
            if (!$this->table) {
                throw new Exception("Table name not defined");
            }
            
            if (!is_numeric($id) || $id <= 0) {
                throw new Exception("Invalid ID provided");
            }
            
            $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
            $result = $this->executeQuery($query, ['id' => $id])->fetch();
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error in getById() for table {$this->table}: " . $e->getMessage());
            return false;
        }
    }
    
    public function create($data) {
        try {
            if (!$this->table) {
                throw new Exception("Table name not defined");
            }
            
            if (empty($data) || !is_array($data)) {
                throw new Exception("Invalid data provided for creation");
            }
            
            // Sanitize input data
            $data = $this->sanitizeInput($data);
            
            // Remove any empty string values and replace with null
            $data = array_map(function($value) {
                return $value === '' ? null : $value;
            }, $data);
            
            // Add timestamps if they don't exist
            if (!isset($data['created_at'])) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }
            if (!isset($data['updated_at'])) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }
            
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $query = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->executeQuery($query, $data);
            
            return $stmt ? $this->db->lastInsertId() : false;
        } catch (Exception $e) {
            error_log("Error in create() for table {$this->table}: " . $e->getMessage());
            return false;
        }
    }
    
    public function update($id, $data) {
        try {
            if (!$this->table) {
                throw new Exception("Table name not defined");
            }
            
            if (!is_numeric($id) || $id <= 0) {
                throw new Exception("Invalid ID provided");
            }
            
            if (empty($data) || !is_array($data)) {
                throw new Exception("Invalid data provided for update");
            }
            
            // Sanitize input data
            $data = $this->sanitizeInput($data);
            
            // Remove any empty string values and replace with null
            $data = array_map(function($value) {
                return $value === '' ? null : $value;
            }, $data);
            
            // Add updated timestamp
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $setParts = [];
            foreach (array_keys($data) as $column) {
                $setParts[] = "{$column} = :{$column}";
            }
            $setClause = implode(', ', $setParts);
            
            // Add ID to parameters
            $data['id'] = $id;
            
            $query = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";
            $stmt = $this->executeQuery($query, $data);
            
            return $stmt ? $stmt->rowCount() : false;
        } catch (Exception $e) {
            error_log("Error in update() for table {$this->table}: " . $e->getMessage());
            return false;
        }
    }
    
    public function delete($id) {
        try {
            if (!$this->table) {
                throw new Exception("Table name not defined");
            }
            
            if (!is_numeric($id) || $id <= 0) {
                throw new Exception("Invalid ID provided");
            }
            
            $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
            $stmt = $this->executeQuery($query, ['id' => $id]);
            
            return $stmt ? $stmt->rowCount() : false;
        } catch (Exception $e) {
            error_log("Error in delete() for table {$this->table}: " . $e->getMessage());
            return false;
        }
    }
    
    public function softDelete($id) {
        try {
            return $this->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        } catch (Exception $e) {
            error_log("Error in softDelete() for table {$this->table}: " . $e->getMessage());
            return false;
        }
    }
    
    public function count($conditions = []) {
        try {
            if (!$this->table) {
                throw new Exception("Table name not defined");
            }
            
            $query = "SELECT COUNT(*) FROM {$this->table}";
            $params = [];
            
            if (!empty($conditions)) {
                $whereClause = [];
                foreach ($conditions as $field => $value) {
                    $whereClause[] = "$field = :$field";
                    $params[$field] = $value;
                }
                $query .= " WHERE " . implode(' AND ', $whereClause);
            }
            
            return $this->executeQuery($query, $params)->fetchColumn();
        } catch (Exception $e) {
            error_log("Error in count() for table {$this->table}: " . $e->getMessage());
            return 0;
        }
    }
    
    public function exists($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                return false;
            }
            
            $query = "SELECT 1 FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
            $stmt = $this->executeQuery($query, ['id' => $id]);
            return $stmt ? $stmt->fetch() !== false : false;
        } catch (Exception $e) {
            error_log("Error in exists() for table {$this->table}: " . $e->getMessage());
            return false;
        }
    }
    
    protected function executeQuery($query, $params = []) {
        try {
            if (empty($query)) {
                throw new Exception("Query cannot be empty");
            }
            
            $stmt = $this->db->prepare($query);
            
            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    // Determine parameter type
                    if (is_int($value)) {
                        $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
                    } elseif (is_bool($value)) {
                        $stmt->bindValue(':' . $key, $value, PDO::PARAM_BOOL);
                    } elseif (is_null($value)) {
                        $stmt->bindValue(':' . $key, $value, PDO::PARAM_NULL);
                    } else {
                        $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
                    }
                }
            }
            
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query execution failed: " . $e->getMessage() . " Query: " . $query);
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }
    
    protected function validateRequired($data, $required) {
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field $field is required", 400);
            }
        }
    }
    
    protected function sanitizeInput($data) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
    
    protected function fetchOne($query, $params = []) {
        try {
            $stmt = $this->executeQuery($query, $params);
            return $stmt ? $stmt->fetch() : false;
        } catch (Exception $e) {
            error_log("Error in fetchOne(): " . $e->getMessage());
            return false;
        }
    }
    
    protected function fetchAll($query, $params = []) {
        try {
            $stmt = $this->executeQuery($query, $params);
            return $stmt ? $stmt->fetchAll() : [];
        } catch (Exception $e) {
            error_log("Error in fetchAll(): " . $e->getMessage());
            return [];
        }
    }
    
    protected function fetchColumn($query, $params = []) {
        try {
            $stmt = $this->executeQuery($query, $params);
            return $stmt ? $stmt->fetchColumn() : false;
        } catch (Exception $e) {
            error_log("Error in fetchColumn(): " . $e->getMessage());
            return false;
        }
    }
    
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    public function commit() {
        return $this->db->commit();
    }
    
    public function rollback() {
        return $this->db->rollBack();
    }
    
    public function getLastInsertId() {
        return $this->db->lastInsertId();
    }
}
?>