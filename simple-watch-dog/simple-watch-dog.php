<?php
/*'
   Plugin Name: Simple Watch Dog
   Description: A plugin to track the users statistics.
   Version: 2.0
   Author: YB
 */

// Prevent direct access to the file
//if (!defined('ABSPATH')) exit;

// Monitor or not the admin entries?
define("WD_SKIP_ADMIN", PRODUCTION_MODE);

// WD database
define('WDDB', 'yb-watch-dog');
define('WDstats',   WDDB . '.wd_visitor_stats');
define('WDremotes', WDDB . '.wd_visitor_remotes');
define('wddb', new wpdb(DB_USER, DB_PASSWORD, WDDB, DB_HOST));

// WD timeout
define('WD_TIMEOUT', 1000);

// WD home
define('WD_HOME', basename(get_home_url()));

require_once (__dir__.'/includes/functions.php');
require_once (__dir__.'/includes/functions_charts.php');
require_once (__dir__.'/includes/admin.php');
