<?php

/**
 * Replace links in text with html links
 *
 * @param  string $text
 * @return string
 */
function auto_link_text($text)
{
   $pattern  = '#\b((http://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
   $callback = create_function('$matches', '
	   $url       = array_shift($matches);
	   $url_parts = parse_url($url);

	   $text = parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);
	   $text = preg_replace("/^www./", "", $text);
	   //$text = preg_replace("/^http:\/\//", "", $text);

	   if (!preg_match("/^http:\/\//", $url))
	   {
			$url  = "http://".$url;
	   }

	   return sprintf(\'<a rel="nofollow" href="%s">%s</a>\', $url, $text);
   ');

   return preg_replace_callback($pattern, $callback, $text);
}

/**
 * Checks the image file exists, returns an html tag to use
 *
 * @author GM
 * @param  string $filename
 * @param  array	$attr 	Attributes to add to the img tag
 * @return string
 */
function img_tag($filename, $attr=null)
{
	if (!file_exists($filename) || !is_file($filename))
	{
		$filename = 'img/uploads/no-image.png';
	}

	$output = '<img src="'. site_url($filename) .'" ';
	if (@$attr)
	{
		foreach($attr as $k=>$v)
		{
			$output .= $k . '="'. $v .'" ';
		}
	}
	$output .= '/>';
	return $output;
}


/**
 * Takes address fields from a user array and returns a string of existant ones seperated by <Br> tags
 **/
function multiline_address($user = array(), $divider='<br>')
{
	$lines = array($user['u_addr_line1'], $user['u_addr_line2'], $user['u_addr_city'], $user['u_addr_pcode']);
	$lines = array_filter($lines, 'strlen');
	$addr = implode($divider, $lines);

	return $addr;
}
