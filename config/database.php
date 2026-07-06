<?php
// اتصال قاعدة البيانات
class Database {
    private static $instance = null;
    private $conn;
    
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    
    // استخدام تصميم Singleton
    private function __construct() {
        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
            
            if ($this->conn->connect_error) {
                throw new Exception("فشل الاتصال بقاعدة البيانات: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8");
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // دالة التنفيذ العامة
    public function execute($sql, $params = [], $types = '') {
        $stmt = $this->conn->prepare($sql);
        
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt;
    }
    
    // دالة جلب سطر واحد
    public function fetchOne($sql, $params = [], $types = '') {
        $stmt = $this->execute($sql, $params, $types);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // دالة جلب جميع النتائج
    public function fetchAll($sql, $params = [], $types = '') {
        $stmt = $this->execute($sql, $params, $types);
        $result = $stmt->get_result();
        $rows = [];
        
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        
        return $rows;
    }
    
    // دالة الإدراج
    public function insert($table, $data) {
        $keys = array_keys($data);
        $values = array_values($data);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        $types = str_repeat('s', count($values));
        
        $sql = "INSERT INTO $table (" . implode(', ', $keys) . ") VALUES ($placeholders)";
        $stmt = $this->execute($sql, $values, $types);
        
        return $this->conn->insert_id;
    }
    
    // دالة التحديث
    public function update($table, $data, $where, $whereParams = [], $whereTypes = '') {
        $setParts = [];
        $values = [];
        $types = '';
        
        foreach ($data as $key => $value) {
            $setParts[] = "$key = ?";
            $values[] = $value;
            $types .= 's';
        }
        
        $values = array_merge($values, $whereParams);
        $types .= $whereTypes;
        
        $sql = "UPDATE $table SET " . implode(', ', $setParts) . " WHERE $where";
        $stmt = $this->execute($sql, $values, $types);
        
        return $stmt->affected_rows;
    }
    
    // دالة الحذف
    public function delete($table, $where, $params = [], $types = '') {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->execute($sql, $params, $types);
        
        return $stmt->affected_rows;
    }
    
    // دالة التحقق من الوجود
    public function exists($table, $where, $params = [], $types = '') {
        $sql = "SELECT COUNT(*) as count FROM $table WHERE $where";
        $result = $this->fetchOne($sql, $params, $types);
        
        return $result['count'] > 0;
    }
    
    // دالة بدء المعاملة
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }
    
    // دالة تأكيد المعاملة
    public function commit() {
        $this->conn->commit();
    }
    
    // دالة التراجع عن المعاملة
    public function rollback() {
        $this->conn->rollback();
    }
    
    // دالة الهروب من المدخلات
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
}

// إنشاء كائن قاعدة البيانات
$db = Database::getInstance();
$conn = $db->getConnection();
?>