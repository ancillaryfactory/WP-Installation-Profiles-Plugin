jQuery(document).ready(function($) {
	$('#profileFilename').val('default.profile');
	
	
	$('#profileForm').submit(function() {
		pluginNames = $('#pluginNames');
		if ( pluginNames.val().length == 0 ) {
			pluginNames.css('border-color','red').focus();
			return false;
		} 
	});
	

	$('#downloadPlugins').click(function() {
		if ( $('#pluginNames').val().length !== 0 ) { 
			$('#downloadPlugins').val('Downloading...');
			$.modal('<div><p>Downloading from the WordPress plugin directory...</p></div>');
		} 
	});
	
	
	$('#choosePluginsButton').click(function() {
		$('#pluginCheckboxForm').modal({
			overlayClose:true,
			opacity:40,
			overlayCss: {backgroundColor:"#000"}
		});
		
		return false;
	});
	
	$('#checkboxContainer > .pluginCheckbox').filter(':odd').css('background-color','#c7c7c7');
	
	
 });