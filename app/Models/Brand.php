<?php
namespace App\Models;

/**
 * Brand Model
 * Handles all brand-related database operations
 */
class Brand extends BaseModel {
    protected $table = 'tbl_brand';
    protected $primaryKey = 'brandID';
    protected $timestamps = false; // Disable timestamps since table doesn't have created_at/updated_at
    
    protected $fillable = [
        'brandName',
        'slug',
        'description',
        'logo',
        'status'
    ];
    
    /**
     * Get all active brands
     */
    public function getActive() {
        return $this->where(['status' => 1], 'brandName ASC');
    }
    
    /**
     * Get brands with product count
     */
    public function getWithProductCount() {
        $sql = "SELECT b.*, COUNT(p.productID) as product_count 
                FROM {$this->table} b 
                LEFT JOIN tbl_product p ON b.brandID = p.brandID AND p.status = 1
                WHERE b.status = 1 
                GROUP BY b.brandID 
                ORDER BY b.brandName ASC";
        
        return $this->query($sql);
    }
    
    /**
     * Get brand by slug
     */
    public function getBySlug($slug) {
        $conditions = ['slug' => $slug, 'status' => 1];
        $result = $this->where($conditions);
        return $result ? $result[0] : null;
    }
    
    /**
     * Create brand with automatic slug generation
     */
    public function createBrand($data) {
        // Generate slug if not provided
        if (empty($data['slug']) && !empty($data['brandName'])) {
            $data['slug'] = $this->generateUniqueSlug($data['brandName']);
        }
        
        return $this->create($data);
    }
    
    /**
     * Update brand
     */
    public function updateBrand($id, $data) {
        // Generate new slug if name changed
        if (!empty($data['brandName'])) {
            $existing = $this->find($id);
            if ($existing && $existing['brandName'] !== $data['brandName']) {
                $data['slug'] = $this->generateUniqueSlug($data['brandName'], $id);
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
     * Delete brand (soft delete by setting status to 0)
     */
    public function deleteBrand($id) {
        return $this->update($id, ['status' => 0]);
    }
    
    /**
     * Get brand statistics
     */
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total_brands,
                    COUNT(CASE WHEN status = 1 THEN 1 END) as active_brands,
                    COUNT(CASE WHEN status = 0 THEN 1 END) as inactive_brands
                FROM {$this->table}";
        
        $result = $this->query($sql);
        return $result ? $result[0] : null;
    }
}
?> 