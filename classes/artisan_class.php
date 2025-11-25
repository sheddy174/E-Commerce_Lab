<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../settings/db_class.php';

/**
 * Artisan Class - Manages artisan profiles and related operations
 * Complete implementation with all CRUD operations
 */
class Artisan extends db_connection
{
    public function __construct()
    {
        parent::db_connect();
    }

    // ==========================================
    // ARTISAN PROFILE CRUD OPERATIONS
    // ==========================================

    /**
     * Create artisan profile
     * @param array $data Profile data
     * @return int|false Artisan ID on success, false on failure
     */
    public function createArtisanProfile($data)
    {
        $stmt = $this->db->prepare("INSERT INTO artisan_profiles (customer_id, shop_name, craft_specialty, years_experience, workshop_location, bio) VALUES (?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("ississ", 
            $data['customer_id'],
            $data['shop_name'],
            $data['craft_specialty'],
            $data['years_experience'],
            $data['workshop_location'],
            $data['bio']
        );
        
        if ($stmt->execute()) {
            $artisan_id = $this->db->insert_id;
            $stmt->close();
            return $artisan_id;
        }
        
        $stmt->close();
        return false;
    }

    /**
     * Get artisan profile by customer ID
     * @param int $customer_id Customer ID
     * @return array|false Profile data on success, false on failure
     */
    public function getArtisanProfile($customer_id)
    {
        $stmt = $this->db->prepare("SELECT ap.*, c.customer_name, c.customer_email, c.customer_contact, c.customer_country, c.customer_city 
                                    FROM artisan_profiles ap 
                                    JOIN customer c ON ap.customer_id = c.customer_id 
                                    WHERE ap.customer_id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result;
    }

    /**
     * Get artisan profile by artisan ID
     * @param int $artisan_id Artisan ID
     * @return array|false Profile data on success, false on failure
     */
    public function getArtisanById($artisan_id)
    {
        $stmt = $this->db->prepare("SELECT ap.*, c.customer_name, c.customer_email, c.customer_contact, c.customer_country, c.customer_city 
                                    FROM artisan_profiles ap 
                                    JOIN customer c ON ap.customer_id = c.customer_id 
                                    WHERE ap.artisan_id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $artisan_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result;
    }

    /**
     * Update artisan profile
     * @param int $artisan_id Artisan ID
     * @param array $data Updated profile data
     * @return bool Success status
     */
    public function updateArtisanProfile($artisan_id, $data)
    {
        $stmt = $this->db->prepare("UPDATE artisan_profiles SET shop_name = ?, craft_specialty = ?, years_experience = ?, workshop_location = ?, bio = ? WHERE artisan_id = ?");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ssissi",
            $data['shop_name'],
            $data['craft_specialty'],
            $data['years_experience'],
            $data['workshop_location'],
            $data['bio'],
            $artisan_id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Delete artisan profile
     * @param int $artisan_id Artisan ID
     * @return bool Success status
     */
    public function deleteArtisanProfile($artisan_id)
    {
        $stmt = $this->db->prepare("DELETE FROM artisan_profiles WHERE artisan_id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $artisan_id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    // ==========================================
    // ARTISAN LISTING & FILTERING
    // ==========================================

    /**
     * Get all artisans (all statuses)
     * @return array|false Array of artisans on success, false on failure
     */
    public function getAllArtisans()
    {
        $sql = "SELECT ap.*, c.customer_name, c.customer_email, c.customer_contact 
                FROM artisan_profiles ap 
                JOIN customer c ON ap.customer_id = c.customer_id 
                ORDER BY ap.created_at DESC";
        
        return $this->db_fetch_all($sql);
    }

    /**
     * Get all verified artisans
     * @return array|false Array of verified artisans on success, false on failure
     */
    public function getVerifiedArtisans()
    {
        $sql = "SELECT ap.*, c.customer_name, c.customer_email, c.customer_contact 
                FROM artisan_profiles ap 
                JOIN customer c ON ap.customer_id = c.customer_id 
                WHERE ap.verification_status = 'verified' 
                ORDER BY ap.rating DESC, ap.created_at DESC";
        
        return $this->db_fetch_all($sql);
    }

    /**
     * Get pending artisans (awaiting verification)
     * @return array|false Array of pending artisans on success, false on failure
     */
    public function getPendingArtisans()
    {
        $sql = "SELECT ap.*, c.customer_name, c.customer_email, c.customer_contact 
                FROM artisan_profiles ap 
                JOIN customer c ON ap.customer_id = c.customer_id 
                WHERE ap.verification_status = 'pending' 
                ORDER BY ap.created_at ASC";
        
        return $this->db_fetch_all($sql);
    }

    /**
     * Get artisans by craft specialty
     * @param string $specialty Craft specialty
     * @return array|false Array of artisans on success, false on failure
     */
    public function getArtisansBySpecialty($specialty)
    {
        $stmt = $this->db->prepare("SELECT ap.*, c.customer_name, c.customer_email, c.customer_contact 
                                    FROM artisan_profiles ap 
                                    JOIN customer c ON ap.customer_id = c.customer_id 
                                    WHERE ap.craft_specialty = ? AND ap.verification_status = 'verified' 
                                    ORDER BY ap.rating DESC");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $specialty);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $result;
    }

    // ==========================================
    // VERIFICATION MANAGEMENT
    // ==========================================

    /**
     * Update verification status
     * @param int $artisan_id Artisan ID
     * @param string $status Verification status (pending, verified, rejected)
     * @return bool Success status
     */
    public function updateVerificationStatus($artisan_id, $status)
    {
        $stmt = $this->db->prepare("UPDATE artisan_profiles SET verification_status = ?, verification_date = NOW() WHERE artisan_id = ?");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $status, $artisan_id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    // ==========================================
    // STATISTICS & ANALYTICS
    // ==========================================

    /**
     * Get artisan statistics (products, sales, revenue)
     * @param int $artisan_id Artisan ID
     * @return array|false Statistics array on success, false on failure
     */
    public function getArtisanStats($artisan_id)
    {
        $sql = "SELECT 
                    COUNT(DISTINCT p.product_id) as total_products,
                    COALESCE(SUM(od.qty), 0) as total_items_sold,
                    COALESCE(SUM(od.qty * p.product_price), 0) as total_revenue
                FROM artisan_profiles ap
                LEFT JOIN products p ON p.artisan_id = ap.artisan_id
                LEFT JOIN orderdetails od ON od.product_id = p.product_id
                WHERE ap.artisan_id = {$artisan_id}";
        
        return $this->db_fetch_one($sql);
    }

    /**
     * Update artisan rating
     * @param int $artisan_id Artisan ID
     * @param float $rating New rating (0.00 - 5.00)
     * @return bool Success status
     */
    public function updateArtisanRating($artisan_id, $rating)
    {
        $stmt = $this->db->prepare("UPDATE artisan_profiles SET rating = ? WHERE artisan_id = ?");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("di", $rating, $artisan_id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Update artisan commission rate
     * @param int $artisan_id Artisan ID
     * @param float $rate Commission rate (0.00 - 100.00)
     * @return bool Success status
     */
    public function updateCommissionRate($artisan_id, $rate)
    {
        $stmt = $this->db->prepare("UPDATE artisan_profiles SET commission_rate = ? WHERE artisan_id = ?");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("di", $rate, $artisan_id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    // ==========================================
    // SEARCH & FILTERING
    // ==========================================

    /**
     * Search artisans by name or shop name
     * @param string $search_term Search term
     * @return array|false Array of matching artisans on success, false on failure
     */
    public function searchArtisans($search_term)
    {
        $search_pattern = "%{$search_term}%";
        $stmt = $this->db->prepare("SELECT ap.*, c.customer_name, c.customer_email, c.customer_contact 
                                    FROM artisan_profiles ap 
                                    JOIN customer c ON ap.customer_id = c.customer_id 
                                    WHERE (c.customer_name LIKE ? OR ap.shop_name LIKE ? OR ap.craft_specialty LIKE ?) 
                                    AND ap.verification_status = 'verified' 
                                    ORDER BY ap.rating DESC");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("sss", $search_pattern, $search_pattern, $search_pattern);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $result;
    }

    /**
     * Get top rated artisans
     * @param int $limit Number of artisans to return
     * @return array|false Array of top artisans on success, false on failure
     */
    public function getTopRatedArtisans($limit = 10)
    {
        $stmt = $this->db->prepare("SELECT ap.*, c.customer_name, c.customer_email, c.customer_contact 
                                    FROM artisan_profiles ap 
                                    JOIN customer c ON ap.customer_id = c.customer_id 
                                    WHERE ap.verification_status = 'verified' 
                                    ORDER BY ap.rating DESC, ap.total_sales DESC 
                                    LIMIT ?");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $result;
    }

    // ==========================================
    // PROFILE IMAGE MANAGEMENT
    // ==========================================

    /**
     * Update artisan profile image
     * @param int $artisan_id Artisan ID
     * @param string $image_path Path to profile image
     * @return bool Success status
     */
    public function updateProfileImage($artisan_id, $image_path)
    {
        $stmt = $this->db->prepare("UPDATE artisan_profiles SET profile_image = ? WHERE artisan_id = ?");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $image_path, $artisan_id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    // ==========================================
    // VERIFICATION DOCUMENTS
    // ==========================================

    /**
     * Add verification document
     * @param int $artisan_id Artisan ID
     * @param string $doc_type Document type
     * @param string $doc_path Document path
     * @return bool Success status
     */
    public function addVerificationDocument($artisan_id, $doc_type, $doc_path)
    {
        $stmt = $this->db->prepare("INSERT INTO artisan_documents (artisan_id, document_type, document_path) VALUES (?, ?, ?)");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("iss", $artisan_id, $doc_type, $doc_path);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Get artisan verification documents
     * @param int $artisan_id Artisan ID
     * @return array|false Array of documents on success, false on failure
     */
    public function getVerificationDocuments($artisan_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM artisan_documents WHERE artisan_id = ? ORDER BY uploaded_at DESC");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $artisan_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $result;
    }
}
?>