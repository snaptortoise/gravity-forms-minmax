/**
 * Gravity Forms MIN/MAX Calculations
 * Version 0.3.0
 * 
 * Add MIN/MAX functions to Gravity Forms calculation
 *
 * Thanks to @michaeldozark for gravityforms-exponent plugin:
 * https://github.com/michaeldozark/gravityforms-exponents
 *
 */

gform.addFilter( 'gform_calculation_result', function( result, formulaField, formId, calcObj ) {

	/**
	 * Only evaluate if the field has MIN/MAX present
	 *
	 * Technically we should be able to run any formulas through this without
	 * breaking them, but this way we save some small amount of processing
	 *
	 * @link https://www.w3schools.com/jsref/jsref_indexof.asp
	 *       Description of `indexOf` method
	 */

	if ( formulaField.formula.indexOf( 'MIN' ) > -1 || formulaField.formula.indexOf( 'MAX' ) > -1  ) {

		/**
		 * Replace field tags with their associated values
		 *
		 * @param int    formId       The ID of the form in use
		 * @param string formula      The value of the "Formula" field entered in
		 *                            the form admin
		 * @param object formulaField The current calculation field object
		 * @var   string fieldFormula
		 */
		let fieldFormula = calcObj.replaceFieldTags( formId, formulaField.formula, formulaField );

		/**
		 * Sanitize the formula in case we have malicious user inputs. This
		 * prevents malicious code getting passed to our `eval` call later in the
		 * function
		 *
		 * We are stripping out anything that is not a number, decimal, space,
		 * parentheses, or simple arithmetical operator.
		 *
		 * @link https://www.w3schools.com/jsref/jsref_replace.asp
		 *       Description of `replace` method
		 */		

		const pattern = /(MIN|MAX)\(([\d\s\W]+)\s*\)/gi,
			matches = fieldFormula.match(pattern);
		
		let replaces = [];		

		for(let i in matches) {			
			let components = /(MIN|MAX)\(([\d\s\W]+)\s*\)/gi.exec(matches[i]);
			let values = components[2].split(',').map((value,index,array) => {
				return parseFloat(eval(value.trim()));
			});			

			if (components[1] == "MIN") replaces.push([matches[i], , Math.min(...values)]);
			if (components[1] == "MAX") replaces.push([matches[i], , Math.max(...values)]);
		}
		
		for(let i in replaces) {
			fieldFormula = fieldFormula.replace(replaces[i][0], replaces[i][2]);
		}
		
		fieldFormula = fieldFormula.replace( /[^0-9\s\n\r\+\-\*\/\^\(\)\.](MIN|MAX)/g, '' );

		/**
		 * Set calculation result equal to evaluated string
		 *
		 * @link https://www.w3schools.com/jsref/jsref_eval.asp
		 *       Description of `eval` function
		 */
		
		result = eval(fieldFormula);

	} 

	return result;

} );