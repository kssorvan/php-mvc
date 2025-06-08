<?php
namespace App\Models;

/**
 * Base Model Class
 * Common database operations for all models
 */
abstract class BaseModel {
    protected $pdo;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $timestamps = true;
    
    public function __construct() {
        $this->pdo = $this->getConnection();
    }
    
    /**
     * Get database connection
     */
    protected function getConnection() {
        try {
            // Check if we already have database constants defined
            if (!defined('DB_HOST')) {
                // Load database configuration
                require_once ROOT_PATH . '/database.php';
                return connectToDatabase();
            } else {
                // Use existing configuration to create connection directly
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                
                return new \PDO($dsn, DB_USER, DB_PASS, $options);
            }
        } catch (\Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Find record by ID
     */
    public function find($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Find error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all records
     */
    public function all($orderBy = null) {
        try {
            $sql = "SELECT * FROM {$this->table}";
            if ($orderBy) {
                $sql .= " ORDER BY {$orderBy}";
            }
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("All records error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create new record
     */
    public function create($data) {
        try {
            // Filter data to only fillable fields
            $filteredData = $this->filterFillableData($data);
            
            // Add timestamps if enabled
            if ($this->timestamps) {
                $filteredData['created_at'] = date('Y-m-d H:i:s');
                $filteredData['updated_at'] = date('Y-m-d H:i:s');
            }
            
            $fields = array_keys($filteredData);
            $placeholders = ':' . implode(', :', $fields);
            
            $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES ({$placeholders})";
            $stmt = $this->pdo->prepare($sql);
            
            if ($stmt->execute($filteredData)) {
                return $this->pdo->lastInsertId();
            }
            return false;
        } catch (\PDOException $e) {
            error_log("Create error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update record
     */
    public function update($id, $data) {
        try {
            // Filter data to only fillable fields
            $filteredData = $this->filterFillableData($data);
            
            // Add updated timestamp if enabled
            if ($this->timestamps) {
                $filteredData['updated_at'] = date('Y-m-d H:i:s');
            }
            
            $setClause = [];
            foreach ($filteredData as $field => $value) {
                $setClause[] = "{$field} = :{$field}";
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE {$this->primaryKey} = :id";
            $stmt = $this->pdo->prepare($sql);
            
            $filteredData['id'] = $id;
            return $stmt->execute($filteredData);
        } catch (\PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete record
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log("Delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find records with conditions
     */
    public function where($conditions = [], $orderBy = null, $limit = null) {
        try {
            $sql = "SELECT * FROM {$this->table}";
            $params = [];
            
            if (!empty($conditions)) {
                $whereClause = [];
                foreach ($conditions as $field => $value) {
                    $whereClause[] = "{$field} = ?";
                    $params[] = $value;
                }
                $sql .= " WHERE " . implode(' AND ', $whereClause);
            }
            
            if ($orderBy) {
                $sql .= " ORDER BY {$orderBy}";
            }
            
            if ($limit) {
                $sql .= " LIMIT {$limit}";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Where query error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Count records
     */
    public function count($conditions = []) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            $params = [];
            
            if (!empty($conditions)) {
                $whereClause = [];
                foreach ($conditions as $field => $value) {
                    $whereClause[] = "{$field} = ?";
                    $params[] = $value;
                }
                $sql .= " WHERE " . implode(' AND ', $whereClause);
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Count error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Execute raw SQL query
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Raw query error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Filter data to only include fillable fields
     */
    protected function filterFillableData($data) {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Get last inserted ID
     */
    public function getLastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollBack() {
        return $this->pdo->rollBack();
    }
}
?> 