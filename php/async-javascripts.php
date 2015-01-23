<?php

function gc_lock_output()
{
	ob_start('add_async_attribute');
}


function add_async_attribute($buffer)
{
	$arr = array('<![CDATA[' => '', ']]>' => '');
	$arr2 = array('/*  */' => '');

	$domd = new DOMDocument();
	libxml_use_internal_errors(true);
	$domd->loadHTML(strtr(strtr($buffer, $arr), $arr2));
	libxml_use_internal_errors(false);

	$items = $domd->getElementsByTagName('script');
	$data = array();

	for ($i = 0; $i < $items->length; $i++) {
		$item = $items->item($i);

		$data[] = array(
			'src' => trim($item->getAttribute('src')),
			'type' => trim($item->getAttribute('type')),
			'innerHTML' => trim($item->nodeValue),
		);
	}

	$aux_buffer = '';

	foreach ($data as $d) {
		$attr = '';
		$async = ' async defer';

		if (!empty($d['src']))
		{
			if (strpos($d['src'], 'jquery.js') !== false || strpos($d['src'], 'jquery-migrate') !== false)
			{
				$async = '';
			}

			$attr .= ' src="' . $d['src'] . '"';
		}

		if (!empty($d['type']))
		{
			$attr .= ' type="' . $d['type'] . '"';
		}

		$content = $d['innerHTML'];

		$aux_buffer .= "<script$async$attr>$content</script>";
	}

	return $aux_buffer;
}

function gc_unlock_output()
{
	ob_end_flush();
}

add_action('wp_head', 'gc_lock_output', 8);
add_action('wp_head', 'gc_unlock_output', 9);

add_action('wp_footer', 'gc_lock_output', 19);
add_action('wp_footer', 'gc_unlock_output', 20);