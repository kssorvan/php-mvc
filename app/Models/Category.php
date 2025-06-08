<?php
namespace App\Models;

/**
 * Category Model
 * Handles all category-related database operations
 */
class Category extends BaseModel {
    protected $table = 'tbl_category';
    protected $primaryKey = 'categoryID';
    protected $timestamps = false; // Disable timestamps to prevent SQL errors
    
    protected $fillable = [
        'catName',
        'slug',
        'description',
        'image',
        'status'
    ];
    
    /**
     * Get all active categories
     */
    public function getActive() {
        return $this->where(['status' => 1], 'catName ASC');
    }
    
    /**
     * Get categories with product count
     */
    public function getWithProductCount() {
        $sql = "SELECT c.*, COUNT(p.productID) as product_count 
                FROM {$this->table} c 
                LEFT JOIN tbl_product p ON c.categoryID = p.categoryID AND p.status = 1
                WHERE c.status = 1 
                GROUP BY c.categoryID 
                ORDER BY c.catName ASC";
        
        return $this->query($sql);
    }
    
    /**
     * Get category by slug
     */
    public function getBySlug($slug) {
        $conditions = ['slug' => $slug, 'status' => 1];
        $result = $this->where($conditions);
        return $result ? $result[0] : null;
    }
    
    /**
     * Create category with automatic slug generation
     */
    public function createCategory($data) {
        // Generate slug if not provided
        if (empty($data['slug']) && !empty($data['catName'])) {
            $data['slug'] = $this->generateUniqueSlug($data['catName']);
        }
        
        return $this->create($data);
    }
    
    /**
     * Update category
     */
    public function updateCategory($id, $data) {
        // Generate new slug if name changed
        if (!empty($data['catName'])) {
            $existing = $this->find($id);
            if ($existing && $existing['catName'] !== $data['catName']) {
                $data['slug'] = $this->generateUniqueSlug($data['catName'], $id);
            }
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Generate unique slug
     */
    private function generateUniqueSlug($name, $excludeId = null) {
        $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
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
        
        $result = $this->query($sql, $params);
        return $result[0]['COUNT(*)'] > 0;
    }
    
    /**
     * Delete category (soft delete by setting status to 0)
     */
    public function deleteCategory($id) {
        return $this->update($id, ['status' => 0]);
    }
    
    /**
     * Get category statistics
     */
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total_categories,
                    COUNT(CASE WHEN status = 1 THEN 1 END) as active_categories,
                    COUNT(CASE WHEN status = 0 THEN 1 END) as inactive_categories
                FROM {$this->table}";
        
        $result = $this->query($sql);
        return $result ? $result[0] : null;
    }
}
?> 