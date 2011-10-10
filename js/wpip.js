jQuery(document).ready(function($) {
	$('#profileFilename').val('default.profile');

		
	$('#importFormWrapper').hide();
	$('#toggleImport').click(function() {
		$('#importFormWrapper').slideToggle();
	});
	
	
	$('#profileForm').submit(function() {
		pluginNames = $('#pluginNames');
		if ( pluginNames.val().length == 0 ) {
			pluginNames.css('border-color','red').focus();
			return false;
		} 
	});
	
	$('#downloadPlugins').click(function() {
		$('#downloadPlugins').val('Downloading...');
		$.modal('<div><p>Downloading from the Wordpress plugin directory<br/><br/><img src="89.gif" /></p></div>');
	});
	
 });