<?php

/**
 * Implements the following shortcode: 
 * [dhwp_simple_shortcode]
 * See: https://codex.wordpress.org/Shortcode_API
 * @return string
 */
function dhwp_simple_shortcode_func(){
	return "DHWP Simple Shortcode Ran";
}
add_shortcode('dhwp_simple_shortcode','dhwp_simple_shortcode_func');


/**
 * Implements the following shortcode: 
 * [dhwp_advanced_shortcode var1="Var1 Value" var2="Var2 Value"]
 * See: https://codex.wordpress.org/Shortcode_API
 * @return string
 */
function dhwp_advanced_shortcode_func($atts){
	$a = shortcode_atts(array(
		'var1' => 'default_value1',
		'var2' => 'default_value2',
	),$atts);

	return "DHWP Advanced Shortcode Ran: ".$a['var1'];
}
add_shortcode('dhwp_advanced_shortcode','dhwp_advanced_shortcode_func');
