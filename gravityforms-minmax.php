<?php
/**
 * Plugin Name: Gravity Forms MIN/MAX Calculation
 * Plugin URI: https://snaptortoise.com?wp-gf-minmax
 * Description: Adds MIN/MAX function support for calculations in number fields
 * Version: 0.3.1
 * Author: SnapTortoise Web Development
 * Author URI: https://snaptortoise.com
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // exit if accessed directly

add_action( 'plugins_loaded', 'gforms_minmax', 20 );

function gforms_minmax() {
	add_action( 'gform_pre_enqueue_scripts', 'gforms_minmax_wp_enqueue_scripts', 10, 2 );
	add_filter( 'gform_calculation_result', 'gforms_minmax_calculation', 10, 5 );
}



function minMaxPrep( $str ) {	
	if( ( strpos( $str, 'MIN(' ) !== false ) || ( strpos( $str, 'MAX(' ) !== false ) ){
		$str = preg_replace( '/MIN\(/', "MIN[", $str );
		$str = preg_replace( '/MAX\(/', "MAX[", $str );
		
		$min_max_bracket_pattern = '/(MIN\[|MAX\[)/';
		preg_match_all( $min_max_bracket_pattern, $str, $min_max_bracket_matches, PREG_OFFSET_CAPTURE );		
		$strIndices = [];	
		
		foreach( $min_max_bracket_matches[0] as $index => $min_max_bracket_match ) {
			$strIndices[] = $min_max_bracket_match[1];
		}
		
		for( $t = 0; $t < count( $strIndices ); $t++ ) {

			$start_counter = 0; 
			$end_counter = 0;

			for( $k = $strIndices[$t]; $k < strlen( $str ); $k++) {
				if( ( substr( $str, $k, 1) === "[" ) || ( substr( $str, $k, 1) === "(" ) ) {
					$start_counter++;
				} else if ( ( substr( $str, $k, 1) === "]" ) || ( substr( $str, $k, 1) === ")" ) ) {
					$end_counter++;
				}

				if( ( $start_counter == $end_counter ) && ( $start_counter != 0 ) ) {
					$str = substr_replace( $str, ']', $k, 1 );
					break;
				}
			}
		}

	}
	return $str;
}



function gforms_math_extensions_calculation( $result, $formula, $field, $form, $entry ) {
	
	if( ( strpos( $formula, 'MIN(' ) !== false ) || ( strpos( $formula, 'MAX(' ) !== false )	) {
		
		global $min_max_clean_pattern;
				
		$min_max_pattern = '/(MIN|MAX)\[([\d\s\)\(\*\/\+\-\.\,])+\s*\]/';
		
		$formula = preg_replace( '/[^0-9\s\n\r\+\-\*\/\^\(\)\.\]\,\MAX[\MIN[\%]/', '', $formula );
					
		$formula = minMaxPrep( $formula );
									
		while( preg_match_all( $min_max_pattern, $formula, $min_max_matches ) ) {
			
			if( isset( $per_matches ) && is_array( $per_matches ) && ( count( $per_matches ) > 0 ) ) {								
				
				foreach( $min_max_matches[0] as $index => $min_max_match ) {
					
					$pre_min_max_match = preg_replace( '/(MIN\[|MAX\[|\])/', '', $min_max_match );
					
					$values = array_map( function( $value ) {			
						return floatval( eval( "return {$value};" ) );
					}, explode( ",", $pre_min_max_match ) );
					
					$formula = str_replace( $min_max_match, ( strpos( $min_max_match, 'MIN[' ) !== false ) ? min( $values ) : max( $values ), $formula );
					
				}
				
			}
			
		}
		
		$formula = preg_replace( '/(MIN\[|MAX\[|\])/', '', $formula );		
				
		$result = eval( "return {$formula};" );
				
	}
	
	return $result;
	
}
add_filter( 'gform_calculation_result', 'gforms_math_extensions_calculation', 10, 5 );



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
