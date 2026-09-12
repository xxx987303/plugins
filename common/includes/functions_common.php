<?php
/**
 * General functions, used in several gits
 * No CMS-specific dependencies, usable both in ProcessWire & WordPress
 */

if (!defined('CLI_MODE')) define('CLI_MODE', empty($_SERVER['HTTP_HOST']));

/**
 * Safe against add_filter add_action
 *'
 * $COLOR == 'ee' means "skip entry/exit" on output
 */
function WD_message(string|array|object $text='', $color='black', $truncate=true) {
    global $WD_messages,  $indent, $prev;
    static $r;

    $messages_keeper = '/tmp/WD_message.html';
    $level0 = preg_match('{YB_message}', WD_getCaller(2)) ? 1 : 0;
    $ee = in_array($text,['entry','exit']);
    if (empty($prev)) $prev = '?';
    
    if (CLI_MODE) {
	if (!$ee) echo WD_getCaller($level0+2).": $text\n";
	return "";
    } elseif ($text == 'print') {    
	if ($messages = (string)@file_get_contents($messages_keeper)){
	    $messages = date('Y-m-d H:i:s',time()) . "<br>$messages";
	}
var_dump($messages); die("?????\n");
	//WD_message(WD_truncatePreserveWord(strip_tags(str_replace(["\n",'<'], [' ',' <'], preg_replace("{\s+}", ' ', $messages)))), 33);
	return $messages;
    }
    // Compact the reply
    if (preg_match('/^<.*>/',$text)) $text = str_replace(['<','>'],['&lt;','&gt;'], $text);

    // Set text colors
    if ($ee) $color='magenta';
    if (is_string($text) && preg_match('{(INSERT|SELECT|DELETE|CREATE|TRUNCATE|DROP) }',$text)) $color = 'blue';
    
    $st_ind  = '/tmp/indent';
    if (!isset($indent)) { $indent = 0; file_put_contents($st_ind, $indent); }
    $indent = file_get_contents($st_ind);

    // Handle 'exit'
    if ($text == 'exit') $indent--;

    // Initialise the messages_keeper
    if (empty(@$WD_messages++)) file_put_contents($messages_keeper, "");

    // Strange... But Claude thinks this is predictable
    if ($indent < 0) {
	error_log("WD_message: indent went negative=$indent! pid=" . getmypid() . " req=" . ($_SERVER['REQUEST_URI'] ?? 'cli'));
	$indent = 0; error_log("WD_message: indent is reset to $indent");
        error_log(print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5), true));
    }

    // Add the message to the messages_keeper
    $r = [' array ( ' => '[',
	  ' ) '       => ']',
	  ' => '      => '=>'];
    $skip_ee = false;
    if (!($ee && $skip_ee) && $text != $prev)
	file_put_contents($messages_keeper,
 			  str_repeat('&nbsp;',2*max(0,$indent)).x("span style=font-weight:bold",WD_getCaller($level0+2)).": ".
 			  x("span style=color:$color",WD_truncatePreserveWord(str_replace(array_keys($r), array_values($r), joinX($text)), 130, $truncate))."<br>\n",
 			  FILE_APPEND);
    // Skip repetive entries
    if (!$ee) $prev = $text;
    
    // Handle 'entry'
    if ($text == 'entry') { $indent++; }

    // Save indent
    file_put_contents($st_ind, $indent);
}

/**
 */
function YB_message($textP='', $level='debug') {
    WD_message($textP);
}

/**
 *   function 	string 	function name. See __FUNCTION__
 *   line 	int 	line number. See __LINE__
 *   file 	string 	file name. See __FILE__
 *   class 	string 	class name. See __CLASS__
 *   object 	object 	object if DEBUG_BACKTRACE_PROVIDE_OBJECT is given
 *   type 	string 	call type. If a method call, "->" is returned
 *                      If a static method call, "::" is returned
 *                      If a function call, nothing is returned
 *   args 	array 	If inside a function, lists the functions arguments
 *                      If inside an included file, lists the included file name(s)
 *                      Unless DEBUG_BACKTRACE_IGNORE_ARGS is given
 */
function WD_getCaller(int $level=2, $args=DEBUG_BACKTRACE_IGNORE_ARGS) {
    $dbt = debug_backtrace($args, $level+1);
    $c = (($x=@$dbt[$level]['class'])    ? $x : '');
    $t = (($x=@$dbt[$level]['type'])     ? $x : '');
    $f = (($x=@$dbt[$level]['function']) ? $x : '');
    $l = (($x=@$dbt[$level]['line'])     ? $x : '');
    $a = (($x=@$dbt[$level]['args'])     ? $x : '');
    $reply = sprintf("%s(%s)", "$c$t$f", $a);
    //$reply = "$c$t$f$a";
    return str_replace(['()','ProcessWire\\'], '', $reply);
}

/**
 */
function WD_truncatePreserveWord($string, $limit = 100, $toTruncate=true) {
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
 * Get all messages from WD_message('print')
 */
function WD_getAllMessages(): string {
    WD_message('entry');
    $messages = "";
    WD_message(var_export(SHOW_MESSAGES,true));
    if (defined('SHOW_MESSAGES' && SHOW_MESSAGES)) {
	$messages = WD_message('print');
	$reply = (!empty($messages)
	    ? x("div class='yb-comments'",
		x("h3","WD_messages...").
		x("code style='font-size:small'", $messages))
	    : "");
	if (empty($messages)) log_error(WD_getCaller(2).' empty messages');
    }else{
	var_dump(SHOW_MESSAGES);
    }
var_dump($messages);
    WD_message($reply);
    WD_message('exit');
    return $messages;
}
    
/**
 * Brackets
 */
function x($tag, $text = '', $text2 = '') {
    if ($text === null) {
        return 'Null';
    } elseif (empty($tag)) {
        return $text;
    } elseif (CLI_MODE) {
        return strip_tags($text);
    }
    
    if ($tag === '!') {
        return str_replace(
            ["\n\n\n","\n\n"],
            "\n",
            ("\n<!-- Start  $text ============================================================= -->\n$text2".
            "\n<!-- End    $text ============================================================= -->\n")
        );
    }
    // 'x' is an "empty" tag
    $tag_clean = preg_replace('/ .*/', '', $tag);
    if ($tag_clean === 'x') {
        return $text;
    }
    
  // Usual brackets
    if (in_array($tag_clean, array('input','img'))) {
        return "<$tag $text />\n";
    }
    
    // Usual brackets
    switch ($tag_clean) {
        case '"':
            if (!isset($reply)) {
                $reply = '"'.str_replace('"', "'", $text).'"';
            }
            return $reply;
        case "'":
            if (!isset($reply)) {
                $reply = "'".str_replace("'", '"', $text)."'";
            }
            return $reply;
        case "[":
            if (!isset($reply)) {
                $reply = '['.$text.']';
            }
        case "(":
            if (!isset($reply)) {
                $reply = '('.$text.')';
            }
        case "<":
            if (!isset($reply)) {
                $reply = '<'.$text.'>';
            }
        case "/":
            if (!isset($reply)) {
                $reply = '/'.$text.'/';
            }
            return $reply;
    }

    // HTML tag as a "bracket"
    list($delim,$postfix) = (preg_match('/^(form|select|style)/i', $tag)
        ? array("\n","")
			   : array("","\n"));
    if (preg_match('/^(div|ul|form|select|style)/i', $tag)) {
        $delim="\n";
    } else {
        $delim="";
    }
    return join($delim, array("", "<$tag>", $text, "</$tag_clean>")).$postfix;
}

