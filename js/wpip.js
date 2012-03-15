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
	
	// stripe profile builder table
	$('#checkboxContainer > .pluginCheckbox').filter(':even').css('background-color','#c7c7c7');
	
	$('#helpTrigger').click(function() {
		$('#contextual-help-link').click();
		return false;
	});
	
	
	$('#wpip_check_all').click (function () {
		$('.pluginCheckbox').attr('checked','checked');
		return false;
	});

	$('#wpip_clear_all').click (function () {
		$('.pluginCheckbox').removeAttr('checked');
		return false;
	});
	
	$('#contextual-help-link').text('Help with WP Install Profiles');
	
 });

/*
* Skeleton V1.1
* Copyright 2011, Dave Gamache
* www.getskeleton.com
* Free to use under the MIT license.
* http://www.opensource.org/licenses/mit-license.php
* 8/17/2011
*/


jQuery(document).ready(function($) {

	/* Tabs Activiation
	================================================== */

	var tabs = $('ul.tabs');

	tabs.each(function(i) {

		//Get all tabs
		var tab = $(this).find('> li > a');
		tab.click(function(e) {

			//Get Location of tab's content
			var contentLocation = $(this).attr('href');

			//Let go if not a hashed one
			if(contentLocation.charAt(0)=="#") {

				e.preventDefault();

				//Make Tab Active
				tab.removeClass('active');
				$(this).addClass('active');

				//Show Tab Content & add active class
				$(contentLocation).show().addClass('active').siblings().hide().removeClass('active');

			}
		});
	});
});