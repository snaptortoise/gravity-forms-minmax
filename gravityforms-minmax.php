<?php
/**
 * Plugin Name: Gravity Forms MIN/MAX Calculation
 * Plugin URI: https://snaptortoise.com?wp-gf-minmax
 * Description: Adds MIN/MAX function support for calculations in number fields
 * Version: 0.1.0
 * Author: Snaptortoise Web Development
 * Author URI: https://snaptortoise.com
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // exit if accessed directly

add_action( 'plugins_loaded', 'gforms_minmax', 20 );

function gforms_minmax() {
	add_action( 'gform_pre_enqueue_scripts', 'gforms_minmax_wp_enqueue_scripts', 10, 2 );
	add_filter( 'gform_calculation_result', 'gforms_minmax_calculation', 10, 5 );
}

function gforms_minmax_calculation( $result, $formula, $field, $form, $entry ) {
	
	if ( false !== strpos( $formula, 'MIN' ) || false !== strpos( $formula, 'MAX' ) ) {

		/**
		 * Sanitize input
		 * 
		 * Removing non-formula related strings * delimit
		 * with an @ symbol.
		 *		 
		 */
		$formula = preg_replace( '@[^0-9\s\n\r\+\-*\/\^\(\)\.](MIN|MAX)@is', '', $formula );

		/**
		 * Filter just the MIN/MAX function calls within the formula
		 */
		preg_match_all( '@((MIN|MAX)\(([\d\.]+)\s*,\s*([\d\.]+)\))@is', $formula, $matches );

		$search = $matches[0];
		$replace = array();

		foreach ( $search as $key => $expression ) {
			if ($matches[2][$key] == "MIN") $replace[] = min($matches[3][$key], $matches[4][$key]);
			if ($matches[2][$key] == "MAX") $replace[] = max($matches[3][$key], $matches[4][$key]);
		} 
		
		/**
		 * Replace instances of MIN(x,y) or MAY(x,y) with
		 * the calcualted values
		 */
		$formula = str_replace( $search, $replace, $formula );

		/**
		 * Evaulate formula and return result
		 */
		$result = eval( "return {$formula};" );
	}
	return $result;
}

/**
 * Enqueue scripts to show calculations on frontend
 */
function gforms_minmax_wp_enqueue_scripts( $form ) {
	if ( GFFormDisplay::has_calculation_field( $form ) ) {

		/**
		 * Load unminified script for debugging purposes
		 *
		 */
		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' );

		wp_enqueue_script( 'gforms-minmax', trailingslashit( plugin_dir_url( __FILE__ ) ) . "gravityforms-minmax{$min}.js", array( 'gform_gravityforms' ), '0.1.0', true );

	}

}