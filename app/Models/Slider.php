<?php
namespace App\Models;

/**
 * Slider Model
 * Handles all slider-related database operations
 */
class Slider extends BaseModel {
    protected $table = 'tbl_slider';
    protected $primaryKey = 'sliderID';
    protected $timestamps = false; // Disable timestamps to prevent SQL errors
    
    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'image_path',
        'link_url',
        'button_text',
        'order_index',
        'status'
    ];
    
    /**
     * Get all active sliders ordered by display order
     */
    public function getActive() {
        return $this->where(['status' => 1], 'order_index ASC');
    }
    
    /**
     * Get featured sliders for homepage
     */
    public function getFeatured($limit = 5) {
        return $this->where(['status' => 1], 'order_index ASC', $limit);
    }
    
    /**
     * Create slider
     */
    public function createSlider($data) {
        // Set default order if not provided
        if (empty($data['order_index'])) {
            $data['order_index'] = $this->getNextOrderIndex();
        }
        
        // Handle image upload if file is provided
        if (isset($data['image']) && is_array($data['image'])) {
            $imageResult = $this->handleImageUpload($data['image'], 'slider');
            if ($imageResult['success']) {
                $data['image_path'] = $imageResult['path'];
            }
            unset($data['image']);
        }
        
        return $this->create($data);
    }
    
    /**
     * Update slider
     */
    public function updateSlider($id, $data) {
        // Handle image upload if file is provided
        if (isset($data['image']) && is_array($data['image'])) {
            $imageResult = $this->handleImageUpload($data['image'], 'slider');
            if ($imageResult['success']) {
                // Delete old image
                $existing = $this->find($id);
                if ($existing && $existing['image_path']) {
                    $this->deleteImage($existing['image_path']);
                }
                $data['image_path'] = $imageResult['path'];
            }
            unset($data['image']);
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Delete slider (soft delete by setting status to 0)
     */
    public function deleteSlider($id) {
        return $this->update($id, ['status' => 0]);
    }
    
    /**
     * Hard delete slider and its image
     */
    public function hardDeleteSlider($id) {
        $slider = $this->find($id);
        if ($slider && $slider['image_path']) {
            $this->deleteImage($slider['image_path']);
        }
        return $this->delete($id);
    }
    
    /**
     * Reorder sliders
     */
    public function reorderSliders($sliderIds) {
        $order = 1;
        foreach ($sliderIds as $sliderId) {
            $this->update($sliderId, ['order_index' => $order]);
            $order++;
        }
        return true;
    }
    
    /**
     * Get next order index
     */
    private function getNextOrderIndex() {
        $sql = "SELECT MAX(order_index) as max_order FROM {$this->table}";
        $result = $this->query($sql);
        return ($result && $result[0]['max_order']) ? $result[0]['max_order'] + 1 : 1;
    }
    
    /**
     * Handle image upload
     */
    private function handleImageUpload($file, $folder = 'slider') {
        $uploadDir = PUBLIC_PATH . "/uploads/{$folder}/";
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type'];
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'error' => 'File too large'];
        }
        
        $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return ['success' => true, 'path' => "{$folder}/{$fileName}"];
        }
        
        return ['success' => false, 'error' => 'Upload failed'];
    }
    
    /**
     * Delete image file
     */
    private function deleteImage($imagePath) {
        $fullPath = PUBLIC_PATH . "/uploads/" . $imagePath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
    
    /**
     * Get slider statistics
     */
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total_sliders,
                    COUNT(CASE WHEN status = 1 THEN 1 END) as active_sliders,
                    COUNT(CASE WHEN status = 0 THEN 1 END) as inactive_sliders
                FROM {$this->table}";
        
        $result = $this->query($sql);
        return $result ? $result[0] : null;
    }
}
?> 