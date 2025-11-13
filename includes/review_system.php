<?php
class ReviewSystem {
    public static function getProductRating($pdo, $product_id) {
        try {
            $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                                  FROM product_reviews WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: ['avg_rating' => 0, 'total_reviews' => 0];
        } catch (Exception $e) {
            return ['avg_rating' => 0, 'total_reviews' => 0];
        }
    }

    public static function getTopRatedProducts($pdo, $limit = 5) {
        $query = "SELECT p.*, c.name as category_name, 
                        AVG(pr.rating) as avg_rating, 
                        COUNT(pr.id) as review_count
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id
                 LEFT JOIN product_reviews pr ON p.id = pr.product_id
                 GROUP BY p.id
                 HAVING review_count > 0
                 ORDER BY avg_rating DESC, review_count DESC 
                 LIMIT ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public static function getBestSellingProducts($pdo, $limit = 5) {
        $query = "SELECT p.*, c.name as category_name, 
                        COUNT(DISTINCT o.id) as order_count,
                        AVG(IFNULL(pr.rating, 0)) as avg_rating
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id
                 LEFT JOIN order_items oi ON p.id = oi.product_id
                 LEFT JOIN orders o ON oi.order_id = o.id
                 LEFT JOIN product_reviews pr ON p.id = pr.product_id
                 GROUP BY p.id
                 ORDER BY order_count DESC, avg_rating DESC 
                 LIMIT ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public static function getUserReviews($pdo, $user_id) {
        $query = "SELECT pr.*, p.name as product_name, p.image
                 FROM product_reviews pr
                 JOIN products p ON pr.product_id = p.id
                 WHERE pr.user_id = ?
                 ORDER BY pr.created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    public static function renderStarRating($rating) {
        $rating = floatval($rating);
        $output = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($rating >= $i) {
                $output .= '★'; // Filled star
            } elseif ($rating >= $i - 0.5) {
                $output .= '☆'; // Half star (using empty for simplicity)
            } else {
                $output .= '☆'; // Empty star
            }
        }
        return $output;
    }
}
