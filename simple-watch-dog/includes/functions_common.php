<?php
/**
 * General functions, used in several gits
 */

/**
 * Safe against add_filter add_action
 *'
 * $COLOR == 'ee' means "skip entry/exit" on output
 */
function WD_message($text='', $color='black', $truncate=true) {
    global $WD_messages,  $indent, $prev;
    static $r;
    
    if ($ee = in_array($text,['entry','exit'])) $color='magenta';
    if (CLI_MODE) {
	if (!$ee) echo WD_getCaller(2)."(): $text\n";
	return;
    }

    $st_ind  = '/tmp/indent';
    $handler = '/tmp/WD_message.html';
    if (!isset($indent)) { $indent = 0; file_put_contents($st_ind, $indent); }
    $indent = file_get_contents($st_ind);

    // Open the handler
    if (empty($WD_messages)) file_put_contents($handler, "<div class='yb-comments'><h3>Messages...</h3>\n<code style='font-size:small'>\n");
    @$WD_messages++;

    // Handle 'print'
    if ($text == 'print') {
	// Close the handler
	file_put_contents($handler, "</code>\n</div>\n", FILE_APPEND);
	return file_get_contents($handler);
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

