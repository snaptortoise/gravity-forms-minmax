/**
 * Gravity Forms MIN/MAX Calculations
 * Version 0.3.1
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
	 * Seaching for MIN(/MAX( to be less likely to try parsing incorrectly syntaxed formulae
	 *
	 * Technically we should be able to run any formulas through this without
	 * breaking them, but this way we save some small amount of processing
	 *
	 * @link https://www.w3schools.com/jsref/jsref_indexof.asp
	 *       Description of `indexOf` method
	 */

	if ( formulaField.formula.indexOf( 'MIN(' ) > -1 || formulaField.formula.indexOf( 'MAX(' ) > -1 ) {

		/**
		 * Replace field tags with their associated values
		 *
		 * @param int    formId       The ID of the form in use
		 * @param string formula      The value of the "Formula" field entered in the form admin
		 *
		 * We are stripping out anything that is not a number, decimal, space, or simple arithmetical operator.
		 * We are excluding any extra nested parenthesis that match might find [^)]
		 *
		 * @param object formulaField The current calculation field object
		 * @var   string fieldFormula
		 *
		 * \W not used and replaced with \)\(\*\\\+\-\.\, so that ] is not mathced
		 */
		let fieldFormula = calcObj.replaceFieldTags( formId, formulaField.formula, formulaField ), pattern = /(MIN|MAX)\[([\d\s\)\(\*\\\+\-\.\,])+\s*\]/gi;
		
		/*
		 *To allow parenthesis within the MIN and MAX arguments, we need to change MIN() and MAX() to MIN[] and MAX[]	
		 *First We change the MIN( to MIN[ and MAX( to MAX[
		 *Then we create an array of the indices of [
		 *Then we loop through the indices 
		 *Foreach we iterate through the subsequent characters.
		 *We count the '(' and the ')' and when they're even we replace the ) with a ]
		*/
		
		fieldFormula = fieldFormula.replace(/MIN\(/gi, "MIN[").replace(/MAX\(/gi, "MAX[");		
		
		var regex = /(MIN\[|MAX\[)/gi, result, fieldFormulaIndices = [];
		
		while ( (result = regex.exec(fieldFormula)) ) {

			fieldFormulaIndices.push(result.index + 3);

		}		
		
		for( var t = 0; t < fieldFormulaIndices.length; t++ ) {
			
			let start_counter = 0;
			let end_counter = 0;
			
			for(var k = fieldFormulaIndices[t]; k < fieldFormula.length; k++) {
				if( ( fieldFormula[k] === "[" ) || ( fieldFormula[k] === "(" ) ) {
					start_counter++;;
				} else if ( fieldFormula[k] === ")" ) {
					end_counter++;
				}
							
				if( (start_counter == end_counter) && (start_counter != 0) ) {
					fieldFormula = fieldFormula.substr(0, k) + "]" + fieldFormula.substr(k + 1);
					break;
				}
			}
		}						
		
		/**
		 * Sanitize the formula in case we have malicious user inputs. This
		 * prevents malicious code getting passed to our `eval` call later in the
		 * function
		 *
		 * @link https://www.w3schools.com/jsref/jsref_replace.asp
		 *       Description of `replace` method
		 *
		 * while is to facilitate parsing nested MIN and MAX functions		 
		 */		
		while ( fieldFormula.indexOf( 'MIN[' ) > -1 || fieldFormula.indexOf( 'MAX[' ) > -1 ) {

			let matches = fieldFormula.match(pattern), replaces = [];		

			for(let i in matches) {			
				let components = /(MIN|MAX)\[([\d\s\)\(\*\\\+\-\.\,])+\s*\]/gi.exec(matches[i]);
				let values = components[0].replace("MAX[", "").replace("MIN[", "").replace("]", "").split(',').map((value,index,array) => {
					return parseFloat(eval(value.trim()));
				});			

				if (components[1] == "MIN") replaces.push([matches[i], Math.min(...values)]);
				if (components[1] == "MAX") replaces.push([matches[i], Math.max(...values)]);
				
			}

			for(let i in replaces) {
				fieldFormula = fieldFormula.replace(replaces[i][0], replaces[i][1]);
			}

			fieldFormula = fieldFormula.replace( /[^0-9\s\n\r\+\-\*\/\^\(\)\.](MIN|MAX)/g, '' );
			
		}
		
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
