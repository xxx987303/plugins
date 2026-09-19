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
function WD_message(string|array|object $text='', string $color='black', bool|int $truncate=true) {
    global $WD_messages,  $indent, $prevl, $lastCaller;
    static $r;

    if (!SHOW_MESSAGES) return "";
    
    $messages_keeper = '/tmp/WD_message.html';
    $level0 = preg_match('{YB_message}', WD_getCaller(2)) ? 1 : 0;
    $ee = in_array($text,['entry','exit']);
    if (empty($prev)) $prev = '?';
    
    if (CLI_MODE) {
	if (!$ee) echo WD_getCaller($level0+2).": $text\n";
	return "";
    } elseif ($text == 'print') {    
	// return the collected messages
	if ($messages = (string)@file_get_contents($messages_keeper)){
	    $messages = x('strong class="wd_time"',date('Y-m-d H:i:s',time())) . "<br>$messages";
	}
	return $messages;
    }
    // Compact the reply
    if (is_string($text) && preg_match('/^<.*>/',$text)) $text = str_replace(['<','>'],['&lt;','&gt;'], $text);

    // Set text colors
    if ($ee) $color='magenta';
    if (is_string($text) && preg_match('{(UPDATE|INSERT|SELECT|DELETE|CREATE|TRUNCATE|DROP) }',$text)) $color = 'blue';
    
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
    $skip_ee = true;
    if (!($ee && $skip_ee) && $text != $prev) { file_put_contents($messages_keeper,
 								  str_replace("\n", " ",
									      str_repeat('&nbsp;',2*max(0,$indent)).
									      x("span class='wd_caller'", ($lastCaller=WD_getCaller($level0+2))) .
									      (is_string($text) && str_starts_with($text, '(') ? '' : ': ').
									      x("span style=color:$color", WD_truncatePreserveWord(joinX($text), 130, $truncate))) . "<br>\n",
								  FILE_APPEND);
	$lastCaller = $skip_ee ? "" : x("span class='wd_caller'",$lastCaller) . x("span style=color:magenta", " : exit").'<br>';
    }
    // Skip repetive entries
    if (!$ee) $prev = $text;
    
    // Handle 'entry'
    if ($text == 'entry') { $indent++; }

    // Save indent
    file_put_contents($st_ind, $indent);
}

/**
 */
function YB_message($textP='', $level='debug') { WD_message($textP); }

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
 *
 * closure:/Users/yb/github/sh_imac.git/site/templates/_hooks.php:23
 */
function WD_getCaller(int $level=2, $args=DEBUG_BACKTRACE_IGNORE_ARGS) {
    $dbt = debug_backtrace($args, $level+1);
    $c = (($x=@$dbt[$level]['class'])    ? $x : '');
    $t = (($x=@$dbt[$level]['type'])     ? $x : '');
    $f = (($x=@$dbt[$level]['function']) ? $x : '');
    $l = (($x=@$dbt[$level]['line'])     ? $x : '');
    $a = (($x=@$dbt[$level]['args'])     ? $x : '');
    if (is_array($a)) $a = joinX($a);
    $reply = sprintf("%s(%s)", "$c$t$f", $a);
    $reply = "$c$t$f$a";
    if (preg_match('{closure:}', $reply)) {
	$parts = explode('/',$reply);
	$reply = sprintf('%s../%s',$parts[0],$parts[count($parts)-1]);
    }
//    return $reply;
    return preg_replace(['/ProcessWire-\>/','{/Users/[a-z]*/}'], ['','~/'], str_replace('ProcessWire\\', '', $reply));
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
function joinX(int|string|array|object|null $a, $skipEmpty=true) : string {

    $escape = function(string $text) : string {
	$r = ['<'=>'&lt;',
	      '>'=>'&gt;'];
	return str_replace(array_keys($r), array_values($r), trim($text));
    };
    
    if (is_object($a)){
	//$a = get_object_vars($a);
	//$a = get_class_vars($a);
	return "Object ".get_class($a);
    }elseif (is_array($a)) {
	$reply = "";
	foreach($a as $k=>$v) {
	    if (is_array($v)) { $reply .= joinX($v, $skipEmpty); continue; }
	    if (empty($v) && $v !== 0 && $v !== '0') continue;
	    if (empty($v)||$k=='comment') continue;
	    $reply .= (is_string($v) ? "$k=>$v, " : joinX($v));
	}
	return '['.preg_replace("/[\n\s]+/", " ", $escape(trim($reply))).']';
    } elseif (is_int($a)) {
	return "$a";
    } elseif (is_string($a)) {
	$reply = preg_replace("/[\n\s]+/", " ", trim($a));
	if (preg_match('/[\<\>]/', $a) && $reply != $a) { var_dump($a); var_dump($reply); exit; }
	return $escape($reply);
    } else {
	var_dump($a);
	die("?????????\n");
    }
}

/**
 * Get all messages from WD_message('print')
 */
function WD_getAllMessages(): string {
    global $lastCaller;
    if (!SHOW_MESSAGES) return "";

    static $css = "
<style>
#wd_messages{
    border-radius: 3em;
    background: gainsboro;
    padding: 1%;
    margin-left: 10%;
    margin-right: 10%;
    font-size: x-small;
}
.wd_time{
    color:chocolate;
}
.wd_caller{
    font-weight:bold;
    color:black;
}
</style>
";

    //WD_message('entry');
    $messages = WD_message('print');
    $reply = (!empty($messages)
	? $css .
	  x("div id=wd_messages",
	    x("h3","WD_messages...").
	    x("code", $messages . $lastCaller))
	: "");
    if (empty($messages)) log_error(WD_getCaller(2).' No messages');
    //WD_message('exit');
    return $reply;
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


/**
 * Return WP user id
 */
function get_WP_User() : int {
    // Map PW user to WP
    static $PW_WP = [  37   => 0,  // guest
		       40   => 0,  // guest
		       41   => 1,  // yb
		       6342 => 2,  // mb
		       6340 => 3,  // tb 
		       6344 => 4,  // ab
		       5972 => 5,  // rb
		       6341 => 6,  // ib
		     //xxxx => 7,  // db
		     //xxxx => 8,  // aaz
		     //xxxx => 9,  // Unknown Russian
    ];
    if (defined('PROCESSWIRE')) {
	$user = \Processwire\Users()->getCurrentUser();
	return isset($PW_WP[$user->id]) ? $PW_WP[$user->id] : 0;
    } elseif (is_user_logged_in()) {
        return wp_get_current_user()->ID;
    } else {
        return 0;
    }
}


/**
 * If a local avatar exists, then use it.
 * Otherwise a avatar will be used, which might be from gravatar 
 */
function get_WP_Avatar($avatar='', $id_or_email='', $size = 96, $default = '', $alt = 'Avatar') {
    YB_message('entry');
    //if (empty($id_or_email)) $id_or_email = get_WP_User();
    $id_or_email = get_WP_User();
    $image = (defined("PROCESSWIRE")
	? \ProcessWire\urls('templates') . "photos/$id_or_email.png"
        :          "/adb/wp-content/uploads/photos/$id_or_email.png");
    YB_message("image($id_or_email) = $image");
    $avatar = "<img alt='$alt' src='$image' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
    $avatar = "<img alt='$alt' src='$image' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
    YB_message("avatar = $avatar");
    YB_message('exit');
    return $avatar;
}
// WP version
if (function_exists('add_filter')) add_filter( 'get_avatar', 'get_WP_Avatar', 10, 5 );
