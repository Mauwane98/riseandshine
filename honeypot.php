
<?php
// Anti-spam honeypot protection utility
class HoneypotProtection {
    private static $honeypot_field = 'website_url'; // Hidden field name
    private static $time_field = 'form_timestamp';
    private static $min_time = 3; // Minimum seconds to fill form
    
    public static function generateFields() {
        $timestamp = time();
        return '
        <!-- Honeypot fields - DO NOT REMOVE -->
        <input type="text" name="' . self::$honeypot_field . '" style="display:none !important; position:absolute; left:-9999px;" tabindex="-1" autocomplete="off">
        <input type="hidden" name="' . self::$time_field . '" value="' . $timestamp . '">
        ';
    }
    
    public static function validateSubmission($post_data) {
        // Check honeypot field
        if (!empty($post_data[self::$honeypot_field])) {
            return false; // Bot detected
        }
        
        // Check form submission time
        if (isset($post_data[self::$time_field])) {
            $form_time = intval($post_data[self::$time_field]);
            $current_time = time();
            if (($current_time - $form_time) < self::$min_time) {
                return false; // Submitted too quickly
            }
        }
        
        return true; // Passed validation
    }
    
    public static function sanitizeInput($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function checkRateLimit($ip, $action = 'contact') {
        $log_file = __DIR__ . '/admin/data/rate_limit.log';
        $current_time = time();
        $time_window = 300; // 5 minutes
        $max_attempts = 3;
        
        // Read existing log
        $attempts = [];
        if (file_exists($log_file)) {
            $lines = file($log_file, FILE_IGNORE_NEW_LINES);
            foreach ($lines as $line) {
                $data = explode('|', $line);
                if (count($data) >= 3) {
                    $log_ip = $data[0];
                    $log_action = $data[1];
                    $log_time = intval($data[2]);
                    
                    // Only count recent attempts for same IP and action
                    if ($log_ip === $ip && $log_action === $action && ($current_time - $log_time) < $time_window) {
                        $attempts[] = $log_time;
                    }
                }
            }
        }
        
        // Check if rate limit exceeded
        if (count($attempts) >= $max_attempts) {
            return false;
        }
        
        // Log this attempt
        $log_entry = $ip . '|' . $action . '|' . $current_time . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
        
        return true;
    }
}
