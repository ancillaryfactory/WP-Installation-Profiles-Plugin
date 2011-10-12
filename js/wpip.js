jQuery(document).ready(function($) {
	$('#profileFilename').val('default.profile');
	
	
	$('#profileForm').submit(function() {
		pluginNames = $('#pluginNames');
		if ( pluginNames.val().length == 0 ) {
			pluginNames.css('border-color','red').focus();
			return false;
		} 
	});
	
	
	
 });