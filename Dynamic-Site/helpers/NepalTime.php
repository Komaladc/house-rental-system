<?php
/**
 * Nepal Time Helper Class
 * Handles time zone conversion for Nepal Standard Time (NST) 
 */

if (!class_exists('NepalTime')) {
class NepalTime {
    
    /**
     * Get current Nepal time in MySQL datetime format
     * Nepal Standard Time is UTC+5:45
     */
    public static function now() {
        // Set timezone to Nepal
        $timezone = new DateTimeZone('Asia/Kathmandu');
        $nepalTime = new DateTime('now', $timezone);
        return $nepalTime->format('Y-m-d H:i:s');
    }
    
    /**
     * Convert any datetime to Nepal time
     */
    public static function toNepalTime($datetime) {
        $timezone = new DateTimeZone('Asia/Kathmandu');
        $date = new DateTime($datetime);
        $date->setTimezone($timezone);
        return $date->format('Y-m-d H:i:s');
    }
    
    /**
     * Add time to current Nepal time
     */
    public static function addTime($interval) {
        $timezone = new DateTimeZone('Asia/Kathmandu');
        $nepalTime = new DateTime('now', $timezone);
        $nepalTime->add(new DateInterval($interval));
        return $nepalTime->format('Y-m-d H:i:s');
    }
    
    /**
     * Add hours to current Nepal time
     */
    public static function addHours($hours) {
        return self::addTime("PT{$hours}H");
    }
    
    /**
     * Add minutes to current Nepal time
     */
    public static function addMinutes($minutes) {
        return self::addTime("PT{$minutes}M");
    }
    
    /**
     * Get Nepal timestamp
     */
    public static function timestamp() {
        return time();
    }
    
    /**
     * Check if a given time is in the past (Nepal time)
     */
    public static function isPast($datetime) {
        $now = self::now();
        return strtotime($datetime) < strtotime($now);
    }
    
    /**
     * Check if a given time is in the future (Nepal time)
     */
    public static function isFuture($datetime) {
        $now = self::now();
        return strtotime($datetime) > strtotime($now);
    }
    
    /**
     * Get formatted Nepal time for display
     */
    public static function format($datetime = null, $format = 'Y-m-d H:i:s') {
        if ($datetime === null) {
            $datetime = self::now();
        }
        
        $timezone = new DateTimeZone('Asia/Kathmandu');
        $date = new DateTime($datetime);
        $date->setTimezone($timezone);
        return $date->format($format);
    }
}
} // End class_exists check
?>
