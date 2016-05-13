<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('base_js'))
{
	function base_js()
	{
		$CI =& get_instance();
		return $CI->config->item('base_js');
	}
}

if ( ! function_exists('base_css'))
{
	function base_css()
	{
		$CI =& get_instance();
		return $CI->config->item('base_css');
	}
}

if ( ! function_exists('base_img'))
{
	function base_img()
	{
		$CI =& get_instance();
		return $CI->config->item('base_img');
	}
}

if ( ! function_exists('base_static'))
{
	function base_static()
	{
		$CI =& get_instance();
		return $CI->config->item('base_static');
	}
}

?>