/**
 * Add MIN/MAX functions to Gravity Forms calculation
 *
 * Thanks to @michaeldozark for gravityforms-exponent plugin:
 * 
 * https://github.com/michaeldozark/gravityforms-exponents
 *
 */


/**
 * Javascript filter
 *
 */
gform.addFilter( 'gform_calculation_result', function( result, formulaField, formId, calcObj ) {

	/**
	 * Only evaluate if the field has a caret in it
	 *
	 * Technically we should be able to run any formulas through this without
	 * breaking them, but this way we save some small amount of processing
	 *
	 * @link https://www.w3schools.com/jsref/jsref_indexof.asp
	 *       Description of `indexOf` method
	 */

	console.log(fieldFormula);
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
		var fieldFormula = calcObj.replaceFieldTags( formId, formulaField.formula, formulaField );

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
		// console.log(fieldFormula);

		var pattern = /(MIN|MAX)\(([\d\.]+)\s*,\s*([\d\.]+)\)/gi;
		var matches = fieldFormula.match(pattern);

		var replaces = [];

		for(let i in matches) {
			//console.log(`#${i}: Matching against ${matches[i]}`)
			// var params = pattern.exec(matches[i]);
			var components = /(MIN|MAX)\(([\d\.]+)\s*,\s*([\d\.]+)\)/gi.exec(matches[i]);

			if (components[1] == "MIN") replaces.push([matches[i], , Math.min(components[2], components[3])]);
			if (components[1] == "MAX") replaces.push([matches[i], , Math.max(components[2], components[3])]);
		}
		
		for(let i in replaces) {
			fieldFormula = fieldFormula.replace(replaces[i][0], replaces[i][2]);
		}

		console.log(fieldFormula)

		// console.log(replaces);
		// fieldFormula.replaces()
		fieldFormula = fieldFormula.replace( /[^0-9\s\n\r\+\-\*\/\^\(\)\.](MIN|MAX)/g, '' );

		/**
		 * Wrap every number with parentheses and replace the caret symbol with
		 * ".pow"
		 */
		// fieldFormula = fieldFormula.replace(/[\d|\d.\d]+/g, function(n){
		// 	return '(' + n + ')'
		// }).replace(/\^|\*\*/g, '.pow');

		/**
		 * Set calculation result equal to evaluated string
		 *
		 * @link https://www.w3schools.com/jsref/jsref_eval.asp
		 *       Description of `eval` function
		 */
		result = eval(fieldFormula);

	} // if ( formulaField.formula.indexOf( '^' ) > -1 || formulaField.formula.indexOf( '**' ) > -1  )

	return result;

} );