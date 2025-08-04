<?php
/**
 * Timezone Configuration for Nepal
 * Sets the default timezone to Nepal Time (NPT)
 */

// Set default timezone to Nepal Time (UTC+5:45)
date_default_timezone_set('Asia/Kathmandu');

// Auto-set timezone when this file is included
if (!ini_get('date.timezone')) {
    ini_set('date.timezone', 'Asia/Kathmandu');
}

// Note: NepalTime class is now defined in helpers/NepalTime.php
// This file only handles timezone configuration

?>
