<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
require_once(INCL_DIR.'emoticons.php'); 
//Finds last occurrence of needle in haystack
//in PHP5 use strripos() instead of this
function _strlastpos ($haystack, $needle, $offset = 0)
{
	$addLen = strlen ($needle);
	$endPos = $offset - $addLen;
	while (true)
	{
		if (($newPos = strpos ($haystack, $needle, $endPos + $addLen)) === false) break;
		$endPos = $newPos;
	}
	return ($endPos >= 0) ? $endPos : false;
}

   function source_highlighter($code)
	 {
	 require_once(INCL_DIR.'geshi/geshi.php');
	 $source = str_replace(array("&#039;", "&gt;", "&lt;", "&quot;", "&amp;"), array("'", ">", "<", "\"", "&"), $code[1]);
		
		if(false!==stristr($code[0],"[php]"))
			$lang2geshi = "php";
		elseif (false!==stristr($code[0],"[sql]"))
			$lang2geshi = "sql";
		elseif (false!==stristr($code[0],"[html]"))
			$lang2geshi = "html4strict";
		else
			$lang2geshi = "txt";
			
		$geshi = new GeSHi($source,$lang2geshi);
		$geshi->set_header_type(GESHI_HEADER_PRE_VALID);
		$geshi->set_overall_style('font: normal normal 100% monospace; color: #000066;', false);
        $geshi->set_line_style('color: #003030;', 'font-weight: bold; color: #006060;', true);
		$geshi->set_code_style('color: #000020;font-family:monospace; font-size:12px;line-height:6px;', true);
		$geshi->enable_classes(false);
		$geshi->set_link_styles(GESHI_LINK, 'color: #000060;');
		$geshi->set_link_styles(GESHI_HOVER, 'background-color: #f0f000;');
		$return = "<div class=\"codetop\">Code</div><div class=\"codemain\">\n";
		$return .= $geshi->parse_code();
		$return .= "\n</div>\n";
			return  $return;
	
	}

        function islocal($link)
        {
            global $TBDEV;
            $flag = false;
            $baseurl  = str_replace(array('http://','www','http://www'),'',$TBDEV['baseurl']);
            $limit = 60;

            if (false !== stristr($link[0], '[url=')) {
                $url = trim($link[1]);
                $title = trim($link[2]);
                if (false !== stristr($link[2], '[img]')) {
                    $flag = true;
                    $title = preg_replace("/\[img](http:\/\/[^\s'\"<>]+(\.(jpg|gif|png)))\[\/img\]/i", "<img src=\"\\1\" alt=\"\" border=\"0\" />", $title);
                }
            } elseif (false !== stristr($link[0], '[url]'))
                $url = $title = trim($link[1]);
            else
                $url = $title = trim($link[2]);

            if (strlen($title) > $limit && $flag == false) {
                $l[0] = substr($title, 0, ($limit / 2));
                $l[1] = substr($title, strlen($title) - round($limit / 3));
                $lshort = $l[0] . "..." . $l[1];
            } else $lshort = $title;
            return "&nbsp;<a href=\"" . ((stristr($url, $baseurl) !== false) ? "" : "http://anonym.to?") . $url . "\" target=\"_blank\">" . $lshort . "</a>";
         

        }

        function format_urls($s)
        {
            return preg_replace_callback("/(\A|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^<>\s]+)/i", "islocal", $s);
        }
        
function validate_imgs($s){
    $start = "(http|https)://";
    $end = "+\.(?:jpe?g|png|gif)";
    preg_match_all("!" . $start . "(.*)" . $end . "!Ui", $s, $result);
    $array = $result[0];
    for ($i = 0; $i < count($array); $i++) {
        $headers = @get_headers($array[$i]);
        if (strpos($headers[0], "200") === false) {
            $s = str_replace("[img]" . $array[$i] . "[/img]", "", $s);
            $s = str_replace("[img=" . $array[$i] . "]", "", $s);
        }
    }
    return $s;
}

            function format_quotes($s)
            {
            preg_match_all('/\\[quote.*?\\]/', $s, $result, PREG_PATTERN_ORDER);
            $openquotecount = count($openquote = $result[0]);
            preg_match_all('/\\[\/quote\\]/', $s, $result, PREG_PATTERN_ORDER);
            $closequotecount = count($closequote = $result[0]);

            if ($openquotecount != $closequotecount) return $s; // quote mismatch. Return raw string...

            // Get position of opening quotes
            $openval = array();
            $pos = -1;

            foreach($openquote as $val)
            $openval[] = $pos = strpos($s, $val, $pos + 1);
            // Get position of closing quotes
            $closeval = array();
            $pos = -1;

            foreach($closequote as $val)
            $closeval[] = $pos = strpos($s, $val, $pos + 1);

            for ($i = 0; $i < count($openval); $i++)
            if ($openval[$i] > $closeval[$i]) return $s; // Cannot close before opening. Return raw string...

            $s = str_replace("[quote]", "<span class='sub'><b>Quote:</b></span><table class='main' border='1' cellspacing='0' cellpadding='10'><tr><td style='border: 1px black dotted'>", $s);
            $s = preg_replace("/\\[quote=(.+?)\\]/", "<span class='sub'><b>\\1 wrote:</b></span><table class='main' border='1' cellspacing='0' cellpadding='10'><tr><td style='border: 1px black dotted'>", $s);
            $s = str_replace("[/quote]", "</td></tr></table><br />", $s);
            return $s;
            }

            function scale($src)
            {
            $max = 350;
            if (!isset($max, $src))
            return;
            $src = str_replace(" ", "%20", $src[1]);
            $info = @getimagesize($src);
            $sw = $info[0];
            $sh = $info[1];
            $addclass = false;
            $max_em = 0.06 * $max;
            if ($max < max($sw, $sh)) {
            if ($sw > $sh)
            $new = array($max_em . "em", "auto");
            if ($sw < $sh)
            $new = array("auto", $max_em . "em");
            $addclass = true;
            } else
            $new = array("auto", "auto");
            $id = mt_rand(0000, 9999);
            if ($new[0] == "auto" && $new[1] == "auto")
            $img = "<img src=\"" . $src . "\" border=\"0\" alt=\"\" />";
            else
            $img = "<img id=\"r" . $id . "\" border=\"0\" alt=\"\" src=\"" . $src . "\" " . ($addclass ? "class=\"resized\"" : "") . " style=\"width:" . $new[0] . ";height:" . $new[1] . ";\" />";
            return $img;
            }

function format_comment($text, $strip_html = true)
{
	global $smilies, $CURUSER, $customsmilies, $TBDEV;
	$s = $text;
  $s = validate_imgs($s);
  $s = str_replace("][" , "] [", $s); 
  unset($text);
  // This fixes the extraneous ;) smilies problem. When there was an html escaped
  // char before a closing bracket - like >), "), ... - this would be encoded
  // to &xxx;), hence all the extra smilies. I created a new :wink: label, removed
  // the ;) one, and replace all genuine ;) by :wink: before escaping the body.
  // (What took us so long? :blush:)- wyz

	$s = str_replace(";)", ":wink:", $s);
	if ($strip_html)
		$s = htmlentities($s, ENT_QUOTES);
        
  if( preg_match( "#function\s*\((.*?)\|\|#is", $s ) )
  {
    $s = str_replace( ":"     , "&#58;", $s );
		$s = str_replace( "["     , "&#91;", $s );
		$s = str_replace( "]"     , "&#93;", $s );
		$s = str_replace( ")"     , "&#41;", $s );
		$s = str_replace( "("     , "&#40;", $s );
		$s = str_replace( "{"	 , "&#123;", $s );
		$s = str_replace( "}"	 , "&#125;", $s );
		$s = str_replace( "$"	 , "&#36;", $s );   
  }
    // [*]
    if (stripos($s, '[*]') !== false)
	  $s = preg_replace("/\[\*\]/", "<img src=\"".$TBDEV['pic_base_url']."list.gif\" alt=\"List\" title=\"List\" class=\"listitem\" />", $s);
	// [b]Bold[/b] 
    if (stripos($s, '[b]') !== false)
        $s = preg_replace('/\[b\](.+?)\[\/b\]/is', "<b>\\1</b>", $s);
	// [i]Italic[/i]
    if (stripos($s, '[i]') !== false)
        $s = preg_replace('/\[i\](.+?)\[\/i\]/is', "<i>\\1</i>", $s);
	// [u]Underline[/u]
    if (stripos($s, '[u]') !== false)
        $s = preg_replace('/\[u\](.+?)\[\/u\]/is', "<span style='text-decoration:underline;'>\\1</span>", $s);
    // [color=blue]Text[/color]
    if (stripos($s, '[color=') !== false) {
        $s = preg_replace('/\[color=([a-zA-Z]+)\](.+?)\[\/color\]/is','<span style="color: \\1">\\2</span>', $s);
   // [color=#ffcc99]Text[/color]
    $s = preg_replace('/\[color=(#[a-f0-9]{6})\](.+?)\[\/color\]/is','<span style="color: \\1">\\2</span>', $s);
    }
    //==Media tag
    if (stripos($s, '[media=') !== false) {
    $s = preg_replace_callback("#\[media=(youtube|liveleak|GameTrailers|vimeo|imdb)\](.+?)\[/media\]#is",
    function($media_tag) {
      return _MediaTag($media_tag[2], $media_tag[1]);
    },
    $s);
    }
    //--img     
    if (stripos($s, '[img') !== false) {      
    $s = preg_replace_callback("/\[img\](http:\/\/[^\s'\"<>]+(\.(jpg|gif|png)))\[\/img\]/i", "scale", $s);
    $s = preg_replace_callback("/\[img=(http:\/\/[^\s'\"<>]+(\.(gif|jpg|png)))\]/i", "scale", $s);
    }
  	// [size=4]Text[/size]
    if (stripos($s, '[size=') !== false)
    $s = preg_replace('/\[size=([1-7])\](.+?)\[\/size\]/is', "<span class='size\\1'>\\2</span>", $s);
	  // [font=Arial]Text[/font]
    if (stripos($s, '[font=') !== false)
    $s = preg_replace('/\[font=([a-zA-Z ,]+)\](.+?)\[\/font\]/is','<span style="font-family: \\1">\\2</span>', $s);
	  // [s]Stroke[/s]
    if (stripos($s, '[s]') !== false)
	  $s = preg_replace("/\[s\](.+?)\[\/s\]/is", "<s>\\1</s>", $s);
    // the [you] tag
    if (stripos($s, '[you]') !== false) {
    $s = preg_replace("/https?:\/\/[^\s'\"<>]*\[you\][^\s'\"<>]*/i", " ", $s);
    $s = preg_replace("/\[you\]/i", $CURUSER['username'], $s);
    }
    // Dynamic Vars
    $s = dynamic_user_vars($s);
    // [Spoiler]TEXT[/Spoiler]
    if (stripos($s, '[spoiler]') !== false)
    $s = preg_replace("/\[spoiler\](.+?)\[\/spoiler\]/is", "<div class=\"smallfont\" align=\"left\">
    <input type=\"button\" value=\"Show\" style=\"width:75px;font-size:10px;margin:0px;padding:0px;\" onclick=\"if (this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display != '') { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = '';this.innerText = ''; this.value = 'Hide'; } else { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = 'none'; this.innerText = ''; this.value = 'Show'; }\" />
    <div style=\"margin: 10px; padding: 10px; border: 1px inset;\" align=\"left\"><div style=\"display: none;\">\\1</div></div></div>", $s);
    // [mcom]Text[/mcom]
    if (stripos($s, '[mcom]') !== false)
    $s = preg_replace("/\[mcom\](.+?)\[\/mcom\]/is","<div style=\"font-size: 18pt; line-height: 50%;\">
    <div style=\"border-color: red; background-color: red; color: white; text-align: center; font-weight: bold; font-size: large;\"><b>\\1</b></div></div>", $s); 
    // [php]php code[/php]
    if (stripos($s, '[php]') !== false)
    $s = preg_replace_callback( "/\[php\](.+?)\[\/php\]/ims", "source_highlighter", $s );
    // [sql]sql code[/sql]
    if (stripos($s, '[sql]') !== false)
    $s = preg_replace_callback( "/\[sql\](.+?)\[\/sql\]/ims", "source_highlighter", $s );
    // [html]html code[/html]
    if (stripos($s, '[html]') !== false)
    $s = preg_replace_callback( "/\[html\](.+?)\[\/html\]/ims", "source_highlighter", $s );  
  	//[mail]mail[/mail]
    if (stripos($s, '[mail]') !== false)
    $s = preg_replace("/\[mail\](.+?)\[\/mail\]/is","<a href=\"mailto:\\1\" targe=\"_blank\">\\1</a>", $s);
	  //[align=(center|left|right|justify)]text[/align]
    if (stripos($s, '[align=') !== false)
    $s = preg_replace("/\[align=([a-zA-Z]+)\](.+?)\[\/align\]/is","<div style=\"text-align:\\1\">\\2</div>", $s); 
	  // Quotes
  	$s = format_quotes($s);
  	// URLs
	  $s = format_urls($s);
    //	$s = format_local_urls($s);
	  // [url=http://www.example.com]Text[/url]
    if (stripos($s, '[url') !== false) {
    $s = preg_replace_callback("/\[url=([^()<>\s]+?)\](.+?)\[\/url\]/is", "islocal", $s);
    // [url]http://www.example.com[/url]
    $s = preg_replace_callback("/\[url\]([^()<>\s]+?)\[\/url\]/is", "islocal", $s);
    }
	  // Linebreaks
	  $s = nl2br($s);
	  // [pre]Preformatted[/pre]
    if (stripos($s, '[pre]') !== false)
	  $s = preg_replace("/\[pre\](.+?)\[\/pre\]/is", "<tt><span style=\"white-space: nowrap;\">\\1</span></tt>", $s);
	  // [nfo]NFO-preformatted[/nfo]
    if (stripos($s, '[nfo]') !== false)
	  $s = preg_replace("/\[nfo\](.+?)\[\/nfo\]/i", "<tt><span style=\"white-space: nowrap;\"><font face='MS Linedraw' size='2' style='font-size: 10pt; line-height: " ."10pt'>\\1</font></span></tt>", $s);
	  // Maintain spacing
	  $s = str_replace("  ", " &nbsp;", $s);
    
    //==Smilies
    if (isset($smilies)) 
    foreach($smilies as $code => $url) {
    $s = str_replace($code, "<img border='0' src=\"{$TBDEV['pic_base_url']}smilies/{$url}\" alt=\"" . htmlspecialchars($code) . "\" />", $s);
    }
    if (isset($customsmilies)) 
    foreach($customsmilies as $code => $url) {
    $s = str_replace($code, "<img border='0' src=\"{$TBDEV['pic_base_url']}smilies/{$url}\" alt=\"" . htmlspecialchars($code) . "\" />", $s);
    }
    return $s;
    }
   
    function _MediaTag( $content, $type ) {
	  global $TBDEV;
	  if( $content == '' OR $type == '' )
 	  return;
	  $return = '';
	
	  switch ($type) 
	  {
 	  case 'youtube':
 	  $return = preg_replace("#^http://(?:|www\.)youtube\.com/watch\?v=([a-zA-Z0-9]+)+?$#i","<object type='application/x-shockwave-flash' height='355' width='425' data='http://www.youtube.com/v/\\1'><param name='movie' value='http://www.youtube.com/v/\\1' /><param name='allowScriptAccess' value='sameDomain' /><param name='quality' value='best' /><param name='bgcolor' value='#FFFFFF' /><param name='scale' value='noScale' /><param name='salign' value='TL' /><param name='FlashVars' value='playerMode=embedded' /><param name='wmode' value='transparent' /></object>", $content);
 	  break;
 	
 	  case 'liveleak':
 	  $return = preg_replace("#^http://(?:|www\.)liveleak\.com/view\?i=([_a-zA-Z0-9]+)+?$#i","<object type='application/x-shockwave-flash' height='355' width='425' data='http://www.liveleak.com/e/\\1'><param name='movie' value='http://www.liveleak.com/e/\\1' /><param name='allowScriptAccess' value='sameDomain' /><param name='quality' value='best' /><param name='bgcolor' value='#FFFFFF' /><param name='scale' value='noScale' /><param name='salign' value='TL' /><param name='FlashVars' value='playerMode=embedded' /><param name='wmode' value='transparent' /></object>", $content);
 	  break;
 	
 	  case 'GameTrailers':
 	  $return = preg_replace("#^http://(?:|www\.)gametrailers\.com/video/([\-_a-zA-Z0-9]+)+?/([0-9]+)+?$#i","<object type='application/x-shockwave-flash' height='355' width='425' data='http://www.gametrailers.com/remote_wrap.php?mid=\\2'><param name='movie' value='http://www.gametrailers.com/remote_wrap.php?mid=\\2' /><param name='allowScriptAccess' value='sameDomain' /> <param name='allowFullScreen' value='true' /><param name='quality' value='high' /></object>", $content);
 	  break;
 	  
 	  case 'imdb':
    $return = preg_replace("#^http://(?:|www\.)imdb\.com/video/screenplay/([_a-zA-Z0-9]+)+?$#i","<div class='\\1'><div style=\"padding: 3px; background-color: transparent; border: none; width:690px;\"><div style=\"text-transform: uppercase; border-bottom: 1px solid #CCCCCC; margin-bottom: 3px; font-size: 0.8em; font-weight: bold; display: block;\"><span onclick=\"if (this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display != '') { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = ''; this.innerHTML = '<b>Imdb Trailer: </b><a href=\'#\' onclick=\'return false;\'>hide</a>'; } else { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = 'none'; this.innerHTML = '<b>Imdb Trailer: </b><a href=\'#\' onclick=\'return false;\'>show</a>'; }\" ><b>Imdb Trailer: </b><a href=\"#\" onclick=\"return false;\">show</a></span></div><div class=\"quotecontent\"><div style=\"display: none;\"><iframe style='vertical-align: middle;' src='http://www.imdb.com/video/screenplay/\\1/player' scrolling='no' width='660' height='490' frameborder='0'></iframe></div></div></div></div>", $content);
 	  break;
 	  
 	  case 'vimeo':
    $return = preg_replace("#^http://(?:|www\.)vimeo\.com/([0-9]+)+?$#i","<object type='application/x-shockwave-flash' width='425' height='355' data='http://vimeo.com/moogaloop.swf?clip_id=\\1&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1'>
    <param name='allowFullScreen' value='true' />
    <param name='allowScriptAccess' value='sameDomain' />
    <param name='movie' value='http://vimeo.com/moogaloop.swf?clip_id=\\1&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1' />
    <param name='quality' value='high' />
    </object>", $content);
    break;
    
 	  default:
 	  $return = 'not found';
	  }

	  return $return;
    } 
  //=== smilie function
  function get_smile()
  {
  global $CURUSER;
  return $CURUSER["smile_until"];
  }
 
////////////09 bbcode function by putyn///////////////
function textbbcode($form,$text,$content="") {
global $CURUSER, $TBDEV;
$custombutton = '';
if(get_smile() != '0')
$custombutton .=" <span style='font-weight:bold;font-size:8pt;'><a href=\"javascript:PopCustomSmiles('".$form."','".$text."')\">[ Custom Smilies ]</a></span>";
$smilebutton = "<a href=\"javascript:PopMoreSmiles('".$form."','".$text."')\">[ More Smilies ]</a>";
$bbcodebody =<<<HTML
<script type="text/javascript">
	var textBBcode = "{$text}";
</script>
<script type="text/javascript" src="./scripts/textbbcode.js"></script>
<div id="hover_pick" style="width:25px; height:25px; position:absolute; border:1px solid #333333; display:none; z-index:20;"></div>
<div id="pickerholder"></div>
<table cellpadding="5" cellspacing="0" align="center"  border="1" class="bb_holder">
  <tr>
    <td width="100%" style="background:#CCCCCC; padding:0" colspan="2"><div style="float:left;padding:4px 0px 0px 2px;">
	<img class="bb_icon" src="{$TBDEV['pic_base_url']}bbcode/bold.png" onclick="tag('b')" title="Bold" alt="B" /> 
	<img class="bb_icon" src="{$TBDEV['pic_base_url']}bbcode/italic.png" onclick="tag('i')" title="Italic" alt="I" /> 
	<img class="bb_icon" src="{$TBDEV['pic_base_url']}bbcode/underline.png" onclick="tag('u')" title="Underline" alt="U" /> 
	<img class="bb_icon" src="{$TBDEV['pic_base_url']}bbcode/strike.png" onclick="tag('s')" title="Strike" alt="S" /> 
	<img class="bb_icon" src="{$TBDEV['pic_base_url']}bbcode/link.png" onclick="clink()" title="Link" alt="Link" /> 
	<img class="bb_icon" src="{$TBDEV['pic_base_url']}bbcode/picture.png" onclick="cimage()" title="Add image" alt="Image"/> 
	<img class="bb_icon" src="{$TBDEV['pic_base_url']}bbcode/colors.png" onclick="colorpicker();" title="Select Color" alt="Colors" /> 
	<img class="bb_icon" src="{$TBDEV['pic_base_url']}bbcode/email.png" onclick="mail()" title="Add email" alt="Email" /> 
HTML;
if($CURUSER['class'] >= UC_MODERATOR)
$bbcodebody .=<<<HTML
	<img class="bb_icon" src="{$TBDEV['pic_base_url']}bbcode/php.png" onclick="tag('php')" title="Add php" alt="Php" /> 
	<img class="bb_icon" src="{$TBDEV['pic_base_url']}bbcode/sql.png" onclick="tag('sql')" title="Add sql" alt="Sql" /> 
	<img class="bb_icon" src="{$TBDEV['pic_base_url']}bbcode/script.png" onclick="tag('html')" title="Add html" alt="Html" /> 
	<img class="bb_icon" src="{$TBDEV['pic_base_url']}bbcode/modcom.png" onclick="tag('mcom')" title="Mod comment" alt="Mod comment" />
HTML;
$bbcodebody .=<<<HTML
</div>
      <div style="float:right;padding:4px 2px 0px 0px;"> <img class="bb_icon" src="{$TBDEV['pic_base_url']}bbcode/align_center.png" onclick="wrap('align','','center')" title="Align - center" alt="Center" /> <img class="bb_icon" src="{$TBDEV['pic_base_url']}bbcode/align_left.png" onclick="wrap('align','','left')" title="Align - left" alt="Left" /> <img class="bb_icon" src="{$TBDEV['pic_base_url']}bbcode/align_justify.png" onclick="wrap('align','','justify')" title="Align - justify" alt="justify" /> <img class="bb_icon" src="{$TBDEV['pic_base_url']}bbcode/align_right.png" onclick="wrap('align','','right')" title="Align - right" alt="Right" /> </div></td>
  </tr>
  <tr>
    <td width="100%" style="background:#CCCCCC; padding:0;" colspan="2"><div style="float:left;padding:4px 0px 0px 2px;">
        <select name="fontfont" id="fontfont"  class="bb_icon" onchange="font('font',this.value);" title="Font face">
          <option value="0">Font</option>
          <option value="Arial" style="font-family: Arial;">Arial</option>
          <option value="Arial Black" style="font-family: Arial Black;">Arial Black</option>
          <option value="Comic Sans MS" style="font-family: Comic Sans MS;">Comic Sans MS</option>
          <option value="Courier New" style="font-family: Courier New;">Courier New</option>
          <option value="Franklin Gothic Medium" style="font-family: Franklin Gothic Medium;">Franklin Gothic Medium</option>
          <option value="Georgia" style="font-family: Georgia;">Georgia</option>
          <option value="Helvetica" style="font-family: Helvetica;">Helvetica</option>
          <option value="Impact" style="font-family: Impact;">Impact</option>
          <option value="Lucida Console" style="font-family: Lucida Console;">Lucida Console</option>
          <option value="Lucida Sans Unicode" style="font-family: Lucida Sans Unicode;">Lucida Sans Unicode</option>
          <option value="Microsoft Sans Serif" style="font-family: Microsoft Sans Serif;">Microsoft Sans Serif</option>
          <option value="Palatino Linotype" style="font-family: Palatino Linotype;">Palatino Linotype</option>
          <option value="Tahoma" style="font-family: Tahoma;">Tahoma</option>
          <option value="Times New Roman" style="font-family: Times New Roman;">Times New Roman</option>
          <option value="Trebuchet MS" style="font-family: Trebuchet MS;">Trebuchet MS</option>
          <option value="Verdana" style="font-family: Verdana;">Verdana</option>
          <option value="Symbol" style="font-family: Symbol;">Symbol</option>
        </select>
        <select name="fontsize" id="fontsize" class="bb_icon" style="padding-bottom:3px;" onchange="font('size',this.value);" title="Font size">
          <option value="0">Font size</option>
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4</option>
          <option value="5">5</option>
          <option value="6">6</option>
          <option value="7">7</option>
        </select>
      </div>
      <div style="float:right;padding:4px 2px 0px 0px;"> <img class="bb_icon" src="pic/bbcode/text_uppercase.png" onclick="text('up')" title="To Uppercase" alt="Up" /> <img class="bb_icon" src="pic/bbcode/text_lowercase.png" onclick="text('low')" title="To Lowercase" alt="Low" /> <img class="bb_icon" src="pic/bbcode/zoom_in.png" onclick="fonts('up')" title="Font size up" alt="S up" /> <img class="bb_icon" src="pic/bbcode/zoom_out.png" onclick="fonts('down')" title="Font size up" alt="S down" /> </div></td>
  </tr>
  <tr>
    <td><textarea id="{$text}" name="{$text}" rows="2" cols="2" style="width:530px; height:250px;font-size:12px;">{$content}</textarea></td>
    <td align="center" valign="top"><table width="0" cellpadding="2" border="1" class="em_holder" cellspacing="2">
         <tr>
          <td align="center"><a href="javascript:em(':-)');"><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/smile1.gif" width="18" height="18" /></a></td>
          <td align="center"><a href="javascript:em(':smile:');"><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/smile2.gif" width="18" height="18" /></a></td>
          <td align="center"><a href="javascript:em(':-D');"><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/grin.gif" width="18" height="18" /></a></td>
          <td align="center"><a href="javascript:em(':w00t:');"><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/w00t.gif" width="18" height="20" /></a></td>
        </tr>
        <tr>
          <td align="center"><a href="javascript:em(':-P');"><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/tongue.gif" width="20" height="20" /></a></td>
          <td align="center"><a href="javascript:em(';-)');"><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/wink.gif" width="20" height="20" /></a></td>
          <td align="center"><a href="javascript:em(':-|');"><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/noexpression.gif" width="18" height="18" /></a></td>
          <td align="center"><a href="javascript:em(':-/');"><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/confused.gif" width="18" height="18" /></a></td>
        </tr>
        <tr>
          <td align="center"><a href="javascript:em(':-(');"><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/sad.gif" width="18" height="18" /></a></td>
          <td align="center"><a href="javascript:em(':baby:');"><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/baby.gif" width="20" height="22" /></a></td>
          <td align="center"><a href="javascript:em(':-O');"><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/ohmy.gif" width="18" height="18" /></a></td>
          <td align="center"><a href="javascript:em('|-)');"><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/sleeping.gif" width="20" height="27" /></a></td>
        </tr>
        <tr>
          <td align="center"><a href="javascript:em(':innocent:');"><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/innocent.gif" width="18" height="22" /></a></td>
          <td align="center"><a href="javascript:em(':unsure:');"><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/unsure.gif" width="20" height="20" /></a></td>
          <td align="center"><a href="javascript:em(':closedeyes:');"><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/closedeyes.gif" width="20" height="20" /></a></td>
          <td align="center"><a href="javascript:em(':cool:');"><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/cool2.gif" width="20" height="20" /></a></td>
        </tr>
        <tr>
          <td align="center"><a href="javascript:em(':thumbsdown:');" ><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/thumbsdown.gif" width="27" height="18" /></a></td>
          <td align="center"><a href="javascript:em(':blush:');" ><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/blush.gif" width="20" height="20" /></a></td>
          <td align="center"><a href="javascript:em(':yes:');"><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/yes.gif" width="20" height="20" /></a></td>
          <td align="center"><a href="javascript:em(':no:');" ><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/no.gif" width="20" height="20" /></a></td>
        </tr>
        <tr>
          <td align="center"><a href="javascript:em(':love:');" ><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/love.gif" width="19" height="19" /></a></td>
          <td align="center"><a href="javascript:em(':?:');" ><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/question.gif" width="19" height="19" /></a></td>
          <td align="center"><a href="javascript:em(':!:');" ><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/excl.gif" width="20" height="20" /></a></td>
          <td align="center"><a href="javascript:em(':idea:');" ><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/idea.gif" width="19" height="19" /></a></td>
        </tr>
        <tr>
          <td align="center"><a href="javascript:em(':arrow:');" ><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/arrow.gif" width="20" height="20" /></a></td>
          <td align="center"><a href="javascript:em(':arrow2:');" ><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/arrow2.gif" width="20" height="20" /></a></td>
          <td align="center"><a href="javascript:em(':hmm:');" ><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/hmm.gif" width="20" height="20" /></a></td>
          <td align="center"><a href="javascript:em(':hmmm:');" ><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/hmmm.gif" width="25" height="23" /></a></td>
        </tr>
        <tr>
          <td align="center"><a href="javascript:em(':huh:');" ><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/huh.gif" width="20" height="20" /></a></td>
          <td align="center"><a href="javascript:em(':rolleyes:');" ><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/rolleyes.gif" width="20" height="20" /></a></td>
          <td align="center"><a href="javascript:em(':kiss:');" ><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/kiss.gif" width="18" height="18" /></a></td>
          <td align="center"><a href="javascript:em(':shifty:');" ><img border="0" alt="Smilies" src="{$TBDEV['pic_base_url']}smilies/shifty.gif" width="20" height="20" /></a></td>
        </tr>
        <tr>
          <td colspan="4" align="center" style="white-space:nowrap;"><span style='font-weight:bold;font-size:8pt;'>{$smilebutton}</span>{$custombutton}</td>
        </tr>
      </table></td></tr></table>
HTML;
	return $bbcodebody;
}

function user_key_codes($key)
        {
            return "/\[$key\]/i";
        }

        function dynamic_user_vars($text)
        {
            global $CURUSER, $TBDEV;
            if (!isset($CURUSER)) 
            return;
            $zone = 0;        // GMT
            //$zone = 3600 * -5; // EST
            $tim = time() + $zone;
            $cu = $CURUSER;
            // unset any variables ya dun want to display, or can't display
            unset($cu['passhash'], $cu['secret'], $cu['editsecret'], $cu['passkey'], $cu['modcomment']);
             
            $bbkeys = array_keys($cu);
            $bbkeys[] = 'curdate';
            $bbkeys[] = 'curtime';
            $bbvals = array_values($cu);
            $bbvals[] = gmdate('F jS, Y', $tim);
            $bbvals[] = gmdate('g:i A', $tim);
            $bbkeys = array_map('user_key_codes', $bbkeys);
            return @preg_replace($bbkeys, $bbvals, $text);
        }

?>
