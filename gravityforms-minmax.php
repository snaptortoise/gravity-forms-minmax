<?php
/**
 * Plugin Name: Gravity Forms MIN/MAX Calculation
 * Plugin URI: https://snaptortoise.com?wp-gf-minmax
 * Description: Adds MIN/MAX function support for calculations in number fields
 * Version: 0.4.0
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

function gforms_minmax_calculation( $result, $formula, $field, $form, $entry ) {
	
	if ( false !== strpos( $formula, 'MIN' ) || false !== strpos( $formula, 'MAX' ) ) {		
		/**
		 * Sanitize input
		 * 
		 * Removing non-formula related strings * delimit
		 * with an @ symbol.
		 *		 
		 */

		// Remove leading & ending parantheses if present
		while (substr($formula,0,1) === '(' && substr($formula,-1) === ')') {
			$formula = substr($formula, 1, -1);
		}

		$formula = preg_replace( '@[^0-9\s\n\r\s\W](MIN|MAX)@is', '', $formula );

		/**
		 * Filter just the MIN/MAX function calls within the formula
		 */
		preg_match_all( '@((MIN|MAX)\(([\d\s\W]+)\s*\))@is', $formula, $matches );		
		
		$search = $matches[0];
		$replace = array();

		$values = explode(",", $matches[3][0]);	
		
		$values = array_map( function($value) {			
			return floatval(eval("return {$value};"));
		}, explode(",", $matches[3][0]));

		foreach ( $search as $key => $expression ) {
			if ($matches[2][$key] == "MIN") $replace[] = min($values);
			if ($matches[2][$key] == "MAX") $replace[] = max($values);
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
		$min = '';

		wp_enqueue_script( 'gforms-minmax', trailingslashit( plugin_dir_url( __FILE__ ) ) . "gravityforms-minmax{$min}.js", array( 'gform_gravityforms' ), '0.1.0', true );

	}

}

add_action( 'gform_admin_pre_render', 'check_formula' );
function check_formula( $form ) {
    ?>
    <script type="text/javascript">
        gform.addFilter( 'gform_is_valid_formula_form_editor', 'check_formula' );
        function check_formula( result, formula ) {					
					if ( formula.indexOf( 'MIN' ) > -1 || formula.indexOf( 'MAX' ) > -1  ) {
						try {
							const pattern = /\(?(MIN|MAX)\(([\d\s\W]+)\s*\)/gi;

							// Remove leading & ending parantheses if present
							while (formula[0] === '(' && formula.slice(-1) === ')') {
								formula = formula.substr(1, formula.length - 2);
							}

							// replace variables with 0 for admin validation
							formula = formula.replace(/\{(.+?)\}/gi,"0");
							matches = formula.match(pattern);
							
							let replaces = [];		

							for(let i in matches) {			
								let components = /\(?(MIN|MAX)\(([\d\s\W]+)\s*\)/gi.exec(matches[i]);			
								let values = components[2].split(',').map((value,index,array) => {			
									return parseFloat(eval(value.trim()));
								});			

								if (components[1] == "MIN") replaces.push([matches[i], , Math.min(...values)]);
								if (components[1] == "MAX") replaces.push([matches[i], , Math.max(...values)]);
							}

							for(let i in replaces) {
								formula = formula.replace(replaces[i][0], replaces[i][2]);
							}

							formula = formula.replace( /[^0-9\s\n\r\+\-\*\/\^\(\)\.](MIN|MAX)/g, '' );					
							result = eval(formula);
						}catch(e) {
							results = false;
						}
					} 

					return result;
				}
    	</script>
    <?php
    //return the form object from the php hook
    return $form;
}