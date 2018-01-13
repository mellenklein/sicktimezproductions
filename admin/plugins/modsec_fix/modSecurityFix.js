/* 
PURPOSE: These functions are used to get around issues with mod_security rules disallowing a post because of suspected SQL injection syntax
USE: call the function checkTextData() onsubmit of a form, it will return true, can be called from within an existing form validation script or directly through an onsubmit call on the form such as: onsubmit="return checkTextData();"
NOTES: 
-IMPORTANT! - make sure if there are multiple tinyMCE fields on the same form, each one has a unique id attribute value
-on processing of the form, you may want to strip the added span tags out of any non tinyMCE fields as they will show up in the field as part of the field value when editing
*/
function checkTextData() {
	//iterate through textareas and inputs checking for the words select/delete with from, break up with a span tag due to loose mod_security rule
	$('textarea').each(function(index) {
		var field_class = '';
		if ($(this).attr('class') != "undefined") {
			field_class = $(this).attr('class');
			//console.log(field_class);
			
			//console.log();
		}
		var field_id = '';
		if ($(this).attr('id') != "undefined") {
			field_id = $(this).attr('id');
		}
		//check if a tinymce textarea because if so, content has to be pulled differently
		var is_mce = false;
		
		if ($(this).hasClass( "mce" )) {
			var input_value = tinyMCE.get(field_id).getContent();
			is_mce = true;
		} else {
			var input_value = $(this).val();	
		}
		//console.log('input_value: ' + input_value);
		//console.log('is mce: ' + is_mce);
		
		if (replaceCondition(input_value)) {
			replace_value = textReplace(input_value, is_mce); 
			//console.log('replace_value: ' + replace_value);
			
			if (is_mce) {
				tinymce.get(field_id).setContent(replace_value);
				//console.log('field id: ' + field_id);
				//console.log(tinymce.get(field_id).setContent(replace_value));
			} else {
				$(this).val(replace_value);		
			}
		}					
	});
	/*$('input[type=text]').each(function(index) {
		var input_value = $(this).val();
		if (replaceCondition(input_value)) {
			input_value = textReplace(input_value);						
			$(this).val(input_value);	
		}					
	});*/
	
	return true;
}

function textReplace(input_value, is_mce) {
	if (is_mce == true) {
		var select_upper = '<span class="modSecFix">SEL</span>ECT';
		var select_lower = '<span class="modSecFix">sel</span>ect';
		var select_ucwords = '<span class="modSecFix">Sel</span>ect';
		var delete_upper = '<span class="modSecFix">DEL</span>ETE';
		var delete_lower = '<span class="modSecFix">del</span>ete';
		var delete_ucwords = '<span class="modSecFix">Del</span>ete';
	} else {
		var select_upper = 'SEL[MSF]ECT';
		var select_lower = 'sel[MSF]ect';
		var select_ucwords = 'Sel[MSF]ect';
		var delete_upper = 'DEL[MSF]ETE';
		var delete_lower = 'del[MSF]ete';
		var delete_ucwords = 'Del[MSF]ete';	
	}
	
	input_value = input_value.replace(/SELECT/g, select_upper); 
	input_value = input_value.replace(/select/g, select_lower);
	input_value = input_value.replace(/Select/g, select_ucwords);
	input_value = input_value.replace(/DELETE/g, delete_upper); 
	input_value = input_value.replace(/delete/g, delete_lower);
	input_value = input_value.replace(/Delete/g, delete_ucwords);
	//console.log(input_value);
	return input_value;
}

function replaceCondition(input_value) {
	var do_replace = false;
	//console.log(input_value);
	var val_lower = input_value.toLowerCase();
	if ((val_lower.indexOf('select') != -1 || val_lower.indexOf('delete') != -1)) {
		do_replace = true;
	}
	
	return do_replace;
}