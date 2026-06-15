<?php

define('WD_TIMEOUT', 1000);

/**
 * array(3) {
 *   [0]=> object(stdClass)#1470 (2) { ["user_id"]=> string(1) "1" ["visits"]=> string(2) "42"}
 *   [1]=> object(stdClass)#1472 (2) { ["user_id"]=> string(1) "3" ["visits"]=> string(1) "4" }
 *   [2]=> object(stdClass)#1475 (2) { ["user_id"]=> string(1) "4" ["visits"]=> string(1) "6" }
 *      }
 */
function wd_set_durations() {
    global $wpdb;
    $table_name = $wpdb->prefix . WDDB;
    
    foreach ($wpdb->get_results("SELECT CONCAT('user_id=',user_id,' remote=',remote) AS x, user_id FROM $table_name GROUP BY x") as $r) {
        $cache = [];
        foreach($wpdb->get_results(sprintf("SELECT id, time,UNIX_TIMESTAMP(time) AS ts, duration FROM %s WHERE user_id=%s ORDER BY ts DESC LIMIT 9999",
                                           $table_name,
                                           $r->user_id)) as $r1) {
            //YB_message(var_export($r,true));
            $cache[] = ['id'  => $r1->id,
                        'ts'  => $r1->ts,
                        'time'=> $r1->time,
                        'duration' => $r1->duration];
        }
        
        $updates = 0;
        foreach($cache as $k=>$v) {
            if ($v['duration'] >= 0) continue;
            if ($k == 0) {
                if ((date('U') - $v['ts']) < WD_TIMEOUT) continue;
                $duration = 0;
            } else {
                $duration = ($d=$cache[$k-1]['ts']-$v['ts']) > WD_TIMEOUT ? 0 : $d;
            }
            $q = sprintf("UPDATE %s SET duration=$duration WHERE id=%d",
                         $table_name,
                         $v['id']);
            $updates++;
            $wpdb->get_results($q);
        }
        echo $r->x . " $updates updates<br>";
    }
}
