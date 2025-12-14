<?php

// Set default timezone to Philippine Time
date_default_timezone_set('Asia/Manila');

function formatPhilippineTime($timestamp, $format = "F d, Y h:i A") {
    if (empty($timestamp)) return 'N/A';
    
    try {
        // Create DateTime object from timestamp
        $date = new DateTime($timestamp);
        
        // Set timezone to Philippine Time
        $date->setTimezone(new DateTimeZone('Asia/Manila'));
        
        // Return formatted date
        return $date->format($format);
    } catch (Exception $e) {
        error_log("Timezone conversion error: " . $e->getMessage());
        return 'Invalid Date';
    }
}

function getCurrentPhilippineTime($format = "Y-m-d H:i:s") {
    $date = new DateTime('now', new DateTimeZone('Asia/Manila'));
    return $date->format($format);
}

function toMySQLDateTime($timestamp = 'now') {
    $date = new DateTime($timestamp, new DateTimeZone('Asia/Manila'));
    return $date->format('Y-m-d H:i:s');
}

function getRelativeTime($timestamp) {
    if (empty($timestamp)) return 'N/A';
    
    try {
        $date = new DateTime($timestamp);
        $date->setTimezone(new DateTimeZone('Asia/Manila'));
        
        $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $diff = $now->diff($date);
        
        if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
        if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
        if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        return 'Just now';
    } catch (Exception $e) {
        error_log("Relative time error: " . $e->getMessage());
        return 'Unknown';
    }
}
?>