<?php
/**
 * General functions, used in several gits
 */

/**
 * Safe against add_filter add_action
 *'
 * $COLOR == 'ee' means "skip entry/exit" on output
 */
function WD_message(string|array|object $text='', $color='black', $truncate=true) {
    global $WD_messages,  $indent, $prev;
    static $r;

    if (CLI_MODE) {
	if (!$ee) echo WD_getCaller(2)."(): $text\n";
	return "";
    }

    // Set text colors
    if ($ee = in_array($text,['entry','exit'])) $color='magenta';
    if (is_string($text) && preg_match('{^(INSERT|SELECT|DELETE|CREATE|TRUNCATE|DROP) }',$text)) $color = 'blue';
    
    $st_ind  = '/tmp/indent';
    if (!isset($indent)) { $indent = 0; file_put_contents($st_ind, $indent); }
    $indent = file_get_contents($st_ind);

    // Open the handler
    $handler = '/tmp/WD_message.html';
    if (empty(@$WD_messages++)) file_put_contents($handler, "");

    // Handle 'print'
    if ($text == 'print') {
        if (!($messages = file_get_contents($handler))) return "";
        return "\n<div class='yb-comments'>\n<h3>".__function__."...</h3>\n<code style='font-size:small'>\n".
               $messages.
               "\n</code>\n</div>\n";
    }

    // Handle 'exit'
    if ($text == 'exit') $indent--;

    // Strange... But Claude thinks this is predictable
    if ($indent < 0) {
	error_log("WD_message: indent went negative=$indent! pid=" . getmypid() . " req=" . ($_SERVER['REQUEST_URI'] ?? 'cli'));
	$indent = 0; error_log("WD_message: indent is reset to $indent");
        error_log(print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5), true));
    }

    // Add the message to the handler
    $r = [' array ( ' => '[',
	  ' ) '       => ']',
	  ' => '      => '=>'];
    if (false){
	file_put_contents($handler,
 			  str_repeat('&nbsp;',2*max(0,$indent)).($ee ? "<span style=font-weight:bold>".WD_getCaller(2)."():</span> ":"").
 			  "<span style=color:$color>".truncatePreserveWord(str_replace(array_keys($r), array_values($r), joinX($text)), 120)."</span>".($ee?"":" ($indent)")."<br>\n",
 			  FILE_APPEND);
    }else{
	$skip_ee = false;
	if (!($ee && $skip_ee) && $text != $prev)
	    file_put_contents($handler,
 			      str_repeat('&nbsp;',2*max(0,$indent))."<span style=font-weight:bold>".WD_getCaller(2)."():</span> ".
 			      "<span style=color:$color>".truncatePreserveWord(str_replace(array_keys($r), array_values($r), joinX($text)), 130, $truncate).
 			      "</span><br>\n",
 			      FILE_APPEND);
	$prev = $text;
    }
    // Handle 'entry'
    if ($text == 'entry') { $indent++; }
    // Save indent
    file_put_contents($st_ind, $indent);
}

/**
 */
function WD_getCaller($level) {
    if (0) $dbt=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,$level+1);
    else   $dbt=debug_backtrace(0,$level+1);
    //print_r($dbt);
    return isset($dbt[$level]['function']) ? $dbt[$level]['function'] : '?';
}

/**
 */
function truncatePreserveWord($string, $limit = 100, $toTruncate=true) {
    // Return the original string if it is already shorter than the limit
    return $string;
    if (!$toTruncate || mb_strlen($string) <= $limit) return $string;

    // Cut the string to the maximum allowed length
    $cutString = mb_substr($string, 0, $limit);

    // Find the last space within the cut string
    $lastSpace = mb_strrpos($cutString, ' ');

    // If a space exists, truncate up to that space; otherwise return the cut string
    if ($lastSpace !== false) return mb_substr($cutString, 0, $lastSpace) . '…';
    return $cutString . '…';
}

/**
 * After many changes it became a sort of "var_dump"
 */
function joinX(int|string|array|object|null $a, $skipEmpty=true) {
    if (is_object($a)) $a = get_object_vars($a);
    if (is_object($a)) $a = get_class_vars($a);
    if (is_array($a)) {
	$r = "";
	foreach($a as $k=>$v) {
	    if (is_array($v)) { $r .= joinX($v, $skipEmpty); continue; }
	    if (empty($v) && $v !== 0 && $v !== '0') continue;
	    if (empty($v)||$k=='comment') continue;
	    $r .= (is_string($v) ? "$k=>$v " : joinX($v));
	}
	return '['.str_replace(' => ','=>',preg_replace("/[\n\s]+/", " ", trim($r))).']';
    } elseif (is_int($a)) {
	return "$a";
    } elseif (is_string($a)) {
	return preg_replace("/[\n\s]+/", " ", trim($a));
    } else {
	var_dump($a);
	die("?????????\n");
    }
}

/**
 * Get all messages from ??_message('print')
 */
function getAllMessages(): string {
    $messages = "";
    if (defined('SHOW_MESSAGES' && SHOW_MESSAGES)) {
	if (function_exists('WD_message')) $messages .= WD_message('print');
	if (function_exists('YB_message')) $messages .= YB_message('print');
    }
    return $messages;
}
    
