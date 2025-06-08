<?php
namespace App\Models;

use App\Helpers\Helper;

/**
 * Product Model
 * Handles all product-related database operations
 */
class Product extends BaseModel {
    protected $table = 'tbl_product';
    protected $primaryKey = 'productID';
    
    protected $fillable = [
        'categoryID',
        'brandID', 
        'productName',
        'slug',
        'description',
        'short_description',
        'price',
        'sale_price',
        'sku',
        'stock_quantity',
        'weight',
        'dimensions',
        'image_path',
        'gallery',
        'featured',
        'status',
        'meta_title',
        'meta_description'
    ];
    
    /**
     * Get all products with category and brand information
     */
    public function getAllWithRelations() {
        $sql = "SELECT p.*, c.catName, b.brandName 
                FROM {$this->table} p 
                LEFT JOIN tbl_category c ON p.categoryID = c.categoryID 
                LEFT JOIN tbl_brand b ON p.brandID = b.brandID 
                WHERE p.status = 1 
                ORDER BY p.created_at DESC";
        
        return $this->query($sql);
    }
    
    /**
     * Get product by ID with relations
     */
    public function getWithRelations($id) {
        $sql = "SELECT p.*, c.catName, b.brandName 
                FROM {$this->table} p 
                LEFT JOIN tbl_category c ON p.categoryID = c.categoryID 
                LEFT JOIN tbl_brand b ON p.brandID = b.brandID 
                WHERE p.{$this->primaryKey} = ? AND p.status = 1";
        
        $result = $this->query($sql, [$id]);
        return $result ? $result[0] : null;
    }
    
    /**
     * Get products by category
     */
    public function getByCategory($categoryId, $limit = null) {
        $conditions = ['categoryID' => $categoryId, 'status' => 1];
        return $this->where($conditions, 'created_at DESC', $limit);
    }
    
    /**
     * Get featured products
     */
    public function getFeatured($limit = 8) {
        $conditions = ['featured' => 1, 'status' => 1];
        return $this->where($conditions, 'created_at DESC', $limit);
    }
    
    /**
     * Search products by name or description
     */
    public function search($query, $limit = null) {
        $sql = "SELECT p.*, c.catName, b.brandName 
                FROM {$this->table} p 
                LEFT JOIN tbl_category c ON p.categoryID = c.categoryID 
                LEFT JOIN tbl_brand b ON p.brandID = b.brandID 
                WHERE (p.productName LIKE ? OR p.description LIKE ?) 
                AND p.status = 1 
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $searchTerm = "%{$query}%";
        return $this->query($sql, [$searchTerm, $searchTerm]);
    }
    
    /**
     * Get products with filters
     */
    public function getFiltered($filters = []) {
        $sql = "SELECT p.*, c.catName, b.brandName 
                FROM {$this->table} p 
                LEFT JOIN tbl_category c ON p.categoryID = c.categoryID 
                LEFT JOIN tbl_brand b ON p.brandID = b.brandID 
                WHERE p.status = 1";
        
        $params = [];
        
        if (!empty($filters['category'])) {
            $sql .= " AND p.categoryID = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['brand'])) {
            $sql .= " AND p.brandID = ?";
            $params[] = $filters['brand'];
        }
        
        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }
        
        // Sorting
        $orderBy = $filters['sort'] ?? 'created_at DESC';
        $sql .= " ORDER BY {$orderBy}";
        
        // Pagination
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT {$filters['limit']}";
            if (!empty($filters['offset'])) {
                $sql .= " OFFSET {$filters['offset']}";
            }
        }
        
        return $this->query($sql, $params);
    }
    
    /**
     * Create product with automatic slug generation
     */
    public function createProduct($data) {
        // Generate slug if not provided
        if (empty($data['slug']) && !empty($data['productName'])) {
            $data['slug'] = $this->generateUniqueSlug($data['productName']);
        }
        
        // Handle image upload if file is provided
        if (isset($data['image']) && is_array($data['image'])) {
            $imageResult = $this->handleImageUpload($data['image']);
            if ($imageResult['success']) {
                $data['image_path'] = $imageResult['path'];
            }
            unset($data['image']);
        }
        
        return $this->create($data);
    }
    
    /**
     * Update product
     */
    public function updateProduct($id, $data) {
        // Generate new slug if name changed
        if (!empty($data['productName'])) {
            $existing = $this->find($id);
            if ($existing && $existing['productName'] !== $data['productName']) {
                $data['slug'] = $this->generateUniqueSlug($data['productName'], $id);
            }
        }
        
        // Handle image upload if file is provided
        if (isset($data['image']) && is_array($data['image'])) {
            $imageResult = $this->handleImageUpload($data['image']);
            if ($imageResult['success']) {
                $data['image_path'] = $imageResult['path'];
            }
            unset($data['image']);
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Generate unique slug
     */
    private function generateUniqueSlug($name, $excludeId = null) {
        $baseSlug = Helper::createSlug($name);
        $slug = $baseSlug;
        $counter = 1;
        
        while ($this->slugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Check if slug exists
     */
    private function slugExists($slug, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Handle image upload
     */
    private function handleImageUpload($file) {
        $errors = Helper::validateImageUpload($file);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $filename = Helper::generateUniqueFilename($file['name']);
        $uploadPath = UPLOAD_PATH . 'products/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $fullPath = $uploadPath . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            return ['success' => true, 'path' => 'products/' . $filename];
        }
        
        return ['success' => false, 'errors' => ['Failed to upload image']];
    }
    
    /**
     * Update stock quantity
     */
    public function updateStock($id, $quantity) {
        $sql = "UPDATE {$this->table} SET stock_quantity = ?, updated_at = NOW() WHERE {$this->primaryKey} = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$quantity, $id]);
    }
    
    /**
     * Decrease stock quantity
     */
    public function decreaseStock($id, $quantity) {
        $sql = "UPDATE {$this->table} SET stock_quantity = stock_quantity - ?, updated_at = NOW() 
                WHERE {$this->primaryKey} = ? AND stock_quantity >= ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$quantity, $id, $quantity]);
    }
    
    /**
     * Get low stock products
     */
    public function getLowStock($threshold = 10) {
        $conditions = ['status' => 1];
        $sql = "SELECT * FROM {$this->table} WHERE status = 1 AND stock_quantity <= ? ORDER BY stock_quantity ASC";
        return $this->query($sql, [$threshold]);
    }
    
    /**
     * Get product statistics
     */
    public function getStats() {
        $stats = [];
        
        // Total products
        $stats['total'] = $this->count(['status' => 1]);
        
        // Out of stock
        $stats['out_of_stock'] = $this->count(['status' => 1, 'stock_quantity' => 0]);
        
        // Low stock (less than 10)
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE status = 1 AND stock_quantity > 0 AND stock_quantity <= 10";
        $stmt = $this->pdo->query($sql);
        $stats['low_stock'] = $stmt->fetchColumn();
        
        // Featured products
        $stats['featured'] = $this->count(['status' => 1, 'featured' => 1]);
        
        return $stats;
    }
}
?> 