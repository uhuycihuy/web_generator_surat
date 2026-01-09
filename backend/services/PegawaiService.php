<?php
/**
 * Pegawai (Employee) Data Service
 * 
 * Handles employee data operations, searches, filtering
 */

class PegawaiService {
    
    private $db;
    private $validator;
    
    /**
     * Initialize service
     * 
     * @param PDO $db Database connection
     * @param RequestValidator $validator Input validator
     */
    public function __construct($db, $validator = null) {
        $this->db = $db;
        $this->validator = $validator;
    }
    
    /**
     * Get all pegawai
     * 
     * @param array $options Filter options (limit, offset, orderBy)
     * @return array List of pegawai
     */
    public function getAll($options = []) {
        $limit = isset($options['limit']) ? (int)$options['limit'] : null;
        $offset = isset($options['offset']) ? (int)$options['offset'] : 0;
        
        // Whitelist allowed order by columns to prevent SQL injection
        $allowedOrderBy = ['nama', 'nip', 'jabatan', 'unit', 'id'];
        $allowedDirection = ['ASC', 'DESC'];
        
        $orderColumn = 'nama';
        $orderDirection = 'ASC';
        
        if (isset($options['orderBy'])) {
            $orderParts = explode(' ', trim($options['orderBy']));
            if (count($orderParts) >= 1 && in_array($orderParts[0], $allowedOrderBy)) {
                $orderColumn = $orderParts[0];
            }
            if (count($orderParts) >= 2 && in_array(strtoupper($orderParts[1]), $allowedDirection)) {
                $orderDirection = strtoupper($orderParts[1]);
            }
        }
        
        try {
            $query = "SELECT id, nip, nama, jabatan, unit, email, no_hp 
                      FROM pegawai 
                      ORDER BY {$orderColumn} {$orderDirection}";
            
            if (!is_null($limit) && $limit > 0) {
                $query .= " LIMIT " . $limit . " OFFSET " . $offset;
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching all pegawai: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get pegawai by ID
     * 
     * @param int $id Pegawai ID
     * @return array|null Pegawai data or null
     */
    public function getById($id) {
        if (!is_numeric($id) || $id <= 0) {
            return null;
        }
        
        try {
            $query = "SELECT id, nip, nama, jabatan, unit, email, no_hp 
                      FROM pegawai 
                      WHERE id = ? LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([(int)$id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error fetching pegawai by ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get pegawai by NIP
     * 
     * @param string $nip Employee NIP
     * @return array|null Pegawai data or null
     */
    public function getByNip($nip) {
        try {
            $query = "SELECT id, nip, nama, jabatan, unit, email, no_hp 
                      FROM pegawai 
                      WHERE nip = ? LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$nip]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching pegawai by NIP: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Search pegawai by name or NIP
     * 
     * @param string $query Search term
     * @return array Search results
     */
    public function search($query) {
        $cleanQuery = trim($query);
        if (empty($cleanQuery) || strlen($cleanQuery) < 2) {
            return [];
        }
        
        // Sanitize search term to prevent injection
        $searchTerm = '%' . addcslashes($cleanQuery, '%_') . '%';
        
        try {
            $sql = "SELECT id, nip, nama, jabatan, unit, email, no_hp 
                    FROM pegawai 
                    WHERE nama LIKE ? OR nip LIKE ? 
                    ORDER BY nama ASC 
                    LIMIT 50";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching pegawai: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get pegawai by unit/department
     * 
     * @param string $unit Unit name
     * @return array Pegawai in unit
     */
    public function getByUnit($unit) {
        if (empty(trim($unit))) {
            return [];
        }
        
        try {
            $query = "SELECT id, nip, nama, jabatan, unit, email, no_hp 
                      FROM pegawai 
                      WHERE unit = ? 
                      ORDER BY nama ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$unit]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching pegawai by unit: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get total count of pegawai
     * 
     * @return int Total count
     */
    public function getTotalCount() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM pegawai");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error counting pegawai: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get unique units/departments
     * 
     * @return array List of units
     */
    public function getUnits() {
        try {
            $query = "SELECT DISTINCT unit 
                      FROM pegawai 
                      WHERE unit IS NOT NULL AND unit != '' 
                      ORDER BY unit ASC";
            
            $stmt = $this->db->query($query);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_column($results, 'unit');
        } catch (PDOException $e) {
            error_log("Error fetching units: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create new pegawai
     * 
     * @param array $data Pegawai data
     * @return int|false Insert ID or false on failure
     */
    public function create($data) {
        if (!is_array($data) || empty($data['nip']) || empty($data['nama'])) {
            return false;
        }
        
        // Basic validation
        $nip = trim($data['nip']);
        $nama = trim($data['nama']);
        
        if (strlen($nip) < 3 || strlen($nama) < 2) {
            return false;
        }
        
        // Check if NIP already exists
        if ($this->getByNip($nip)) {
            error_log("NIP already exists: " . $nip);
            return false;
        }
        
        // Validate data if validator available
        if ($this->validator) {
            $rules = [
                'nip' => 'required|string',
                'nama' => 'required|string',
                'jabatan' => 'string',
                'unit' => 'string',
                'email' => 'email',
                'no_hp' => 'phone',
            ];
            
            try {
                if (method_exists($this->validator, 'validate')) {
                    $this->validator->validate($data, $rules);
                }
            } catch (Exception $e) {
                error_log("Validation error creating pegawai: " . $e->getMessage());
                return false;
            }
        }
        
        try {
            $query = "INSERT INTO pegawai (nip, nama, jabatan, unit, email, no_hp, created_at) 
                      VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($query);
            $success = $stmt->execute([
                $nip,
                $nama,
                trim($data['jabatan'] ?? ''),
                trim($data['unit'] ?? ''),
                trim($data['email'] ?? ''),
                trim($data['no_hp'] ?? ''),
            ]);
            
            return $success ? (int)$this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Error creating pegawai: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update pegawai data
     * 
     * @param int $id Pegawai ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update($id, $data) {
        if (!is_numeric($id) || $id <= 0 || !is_array($data) || empty($data)) {
            return false;
        }
        
        // Check if record exists
        if (!$this->getById($id)) {
            return false;
        }
        
        try {
            $fields = [];
            $values = [];
            
            $allowedFields = ['nip', 'nama', 'jabatan', 'unit', 'email', 'no_hp'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $value = trim($data[$field]);
                    
                    // Skip empty required fields
                    if (in_array($field, ['nip', 'nama']) && empty($value)) {
                        continue;
                    }
                    
                    // Check NIP uniqueness if updating NIP
                    if ($field === 'nip' && !empty($value)) {
                        $existing = $this->getByNip($value);
                        if ($existing && $existing['id'] != $id) {
                            error_log("NIP already exists: " . $value);
                            return false;
                        }
                    }
                    
                    $fields[] = "`$field` = ?";
                    $values[] = $value;
                }
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $values[] = (int)$id;
            
            $query = "UPDATE pegawai SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($query);
            
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Error updating pegawai: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete pegawai
     * 
     * @param int $id Pegawai ID
     * @return bool Success status
     */
    public function delete($id) {
        if (!is_numeric($id) || $id <= 0) {
            return false;
        }
        
        // Check if record exists
        if (!$this->getById($id)) {
            return false;
        }
        
        try {
            $query = "DELETE FROM pegawai WHERE id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([(int)$id]);
        } catch (PDOException $e) {
            error_log("Error deleting pegawai: " . $e->getMessage());
            return false;
        }
    }
}
?>
