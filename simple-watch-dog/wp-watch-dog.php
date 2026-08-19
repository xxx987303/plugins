<?php
/*
Plugin Name: Watch Dog
Description: A plugin to count visitors and track user statistics. <br>Other "Visitor Statistics" plugins (if any) which might be here are just for the author reference, those might be deactivated/deleted at any moment.
Version: 2.0
Author: YB
*/

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Monitor or not the admin entries?
define("WD_SKIP_ADMIN", true);

require_once ('includes/functions.php');
require_once ('includes/functions_charts.php');
require_once ('includes/admin.php');
?>
