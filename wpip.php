<?php

/*



Plugin Name: Installation Profiles
Plugin URI: https://github.com/ancillaryfactory/WP-Installation-Profiles-Plugin
Description: Download collections of plugins. Go to Plugins -> Bulk Install Profiles
Version: 3.0
Author: Jon Schwab
Author URI: http://www.ancillaryfactory.com
License: GPL2




Copyright 2012    (email : jon@ancillaryfactory.com)



    This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See th
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA


*/



$plugin = plugin_basename(__FILE__); 

require(WP_PLUGIN_DIR . '/install-profiles/includes/process-profiles.php');

function wpip_installation_profile_admin_actions() {
	if (current_user_can('activate_plugins')) {
		global $wpip_page;
		$wpip_page = add_submenu_page( 'plugins.php', 'Installation Profiles', 'Bulk Install Profiles', 'install_plugins', 'installation_profiles', 'wpip_installation_profile_admin' );		
		$text = '<h3>How to add new plugins:</h3>

	<p>Search the <a href="http://wordpress.org/extend/plugins/" target="_blank">WordPress Plugin Directory</a> and copy the slug from the plugin&apos;s listing page. For example, use the following text for <em>WP Super Cache</em>:</p><img src="'. plugins_url('plugin-url.png',__FILE__) . '" style="border:solid 1px #d8d8d8"/><br/>
	<p>Plugin names may be added with or without hyphens (e.g. <em>wp-super-cache</em> = <em>wp super cache</em>).</p>';
	
		add_action( 'admin_print_styles-' . $wpip_page, 'wpip_admin_styles' );
		add_action('admin_footer-'. $wpip_page, 'wpip_profile_select' );
		// add_contextual_help( $wpip_page, $text );

		add_action('load-'.$wpip_page, 'wpip_add_help_tab');

	}

}

function wpip_add_help_tab () {
    global $wpip_page;
    $screen = get_current_screen();

    if ( $screen->id != $wpip_page )
        return;

    // Add my_help_tab if current screen is My Admin Page
    $screen->add_help_tab( array(
        'id'	=> 'wpip_how_to_add',
        'title'	=> __('How to add plugins'),
        'content'	=> '<h3>How to add new plugins:</h3>

	<p>Search the <a href="http://wordpress.org/extend/plugins/" target="_blank">WordPress Plugin Directory</a> and copy the slug from the plugin&apos;s listing page. For example, use the following text for <em>WP Super Cache</em>:</p><img src="'. plugins_url('plugin-url.png',__FILE__) . '" style="border:solid 1px #d8d8d8"/><br/>
	<p>Plugin names may be added with or without hyphens (e.g. <em>wp-super-cache</em> = <em>wp super cache</em>).</p>',
    ) );

    $screen->add_help_tab( array(
        'id'	=> 'wpip_online',
        'title'	=> __('Save profiles online'),
        'content'	=> '<h3>Save your profiles online:</h3>

       <ol>
        	<li>Go to <a href="http://plugins.ancillaryfactory.com" target="_blank">plugins.ancillaryfactory.com</a> and register for an account.</li>
        	<li>Browse other install profiles or create new ones.</li>
        	<li>Click the "<strong>Import Profiles</strong>" tab below, and enter your WPIP username to automatically import your profiles.</li>
       </ol> 	
        ',
    ) );



}




function wpip_installation_profile_admin_init() {
       wp_register_style( 'wpipStylesheet', plugins_url('css/wpip.css', __FILE__) );
	   wp_register_script( 'wpipScripts', plugins_url('js/wpip.js', __FILE__),'jquery',false,true );
	   wp_register_script( 'wpipModal', plugins_url('js/jquery.simplemodal.1.4.1.min.js', __FILE__),'jquery',false,true );
	 //  wp_register_script( 'wpipTabs', plugins_url('js/tabs.js', __FILE__),'jquery',false,true );
   }



function wpip_admin_styles() {
       wp_enqueue_style( 'wpipStylesheet' );
	   wp_enqueue_script( 'wpipScripts' );
	   wp_enqueue_script( 'wpipModal' );
	//   wp_enqueue_script( 'wpipTabs' );
   }



add_action('admin_init', 'wpip_installation_profile_admin_init' );
add_action('admin_menu', 'wpip_installation_profile_admin_actions');





if ( isset($_POST['saveProfile']) || isset($_POST['downloadPlugins']) ) {
	add_action('admin_notices', 'wpip_save_profile' );
}



// download one of the existing profiles

if ( isset($_GET['download']) ) {
	add_action('admin_init', 'wpip_download_profile' );
}



// download custom profile

if ( isset($_POST['customProfileSubmit'] ) ) {
	add_action('admin_init', 'wpip_build_custom_profile' );
}





if ( isset($_POST['importSubmit'] ) ) {
	add_action('admin_notices', 'wpip_import_profile' );
}





if ( isset($_POST['downloadPlugins'] ) ) {
	add_action('admin_notices', 'wpip_fetch_plugins' );
}



if ( isset($_POST['apiSubmit'] ) ) {
	add_action('admin_notices', 'wpip_import_from_wpip_api' );
}





function wpip_installation_profile_admin() { 

	// read data from default profile
	$readDefaults = fopen(WP_PLUGIN_DIR . '/install-profiles/profiles/default.profile',"r");
	$defaultLines = fread($readDefaults, filesize(WP_PLUGIN_DIR . '/install-profiles/profiles/default.profile'));
	fclose($readDefaults);


	$dir = WP_PLUGIN_DIR . '/install-profiles/profiles';
	$profilesList = scandir($dir);

?>



<div class="wrap"> 

 	<div id="icon-tools" class="icon32" style="float:left"></div>

	<h2 style="float:left;">Bulk Install Profiles</h2>

	<div id="wpipFormWrapper" > <!-- class="postbox" -->


	<!-- tabs -->
	<ul class="tabs" style="margin-top:22px">
	  <!-- Give href an ID value of corresponding "tabs-content" <li>s -->
	  <li><a class="active" href="#download">Download Plugins</a></li>
	  <li><a href="#import">Import Profiles</a></li>
	  <li><a href="#export">Export Profiles</a></li>
	</ul>
	<!-- end tabs -->

	<ul class="tabs-content" style="margin-left:10px">
	
		
		<li id="download" class="active">
<!-- <h3 style="font-size:16px;margin-top:24px;margin-bottom:20px;padding: 0 5px 5px;">Download Plugins</h3> -->
<form method="post" action="admin.php?page=installation_profiles" id="profileForm">

		<p>

		<?php  

			if ( wpip_is_windows() ) {
				$pattern = '(txt$)';
			} else {
				$pattern = '(profile$)';
			}

		 ?>


		<h3 style="font-size:16px; padding: 3px 0 5px;">Choose a profile:</h3>
		<select id="profileFilename" name="profileFilename">

			<?php 
			foreach ( $profilesList as $profileFile ) {
				if ( preg_match( $pattern, $profileFile) ) {
					$nameLength = stripos($profileFile, '.');
					$name = substr($profileFile,0,$nameLength);
					echo '<option value="' . $profileFile . '">' . esc_attr($name) . '</option>';
				}

			}			

		?>
		</select>
		<br/><br/>

		<strong>Or save this profile as:</strong><br/>
			<input type="text" name="profileName" id="profileName" style="width:200px;" placeholder="Name"/>
		</p>

		

		<p><strong>Plugins</strong> <em>(one per line)</em>: <span><a style="font-size:10px;position:relative;top:-2px;margin-left:45px" href="#" id="helpTrigger">How to add plugins</a></span><br/>
			<textarea name="pluginNames" id="pluginNames" rows="15" cols="46"><?php print esc_textarea($defaultLines); ?></textarea>
		</p>

		

		<p style="margin-top: 20px;">
		<?php wp_nonce_field('plugins_to_download','wpip_submit'); ?>
		<input class="button-secondary" type="submit" name="saveProfile" value="Save profile" style="padding:5px"/>&nbsp;&nbsp;
		<input class="button-primary" type="submit" name="downloadPlugins" value="Download plugins and save profile" style="padding:5px" id="downloadPlugins"/>
		</p>
	</form>
</li> <!-- end Download tab -->


		<li id="import">

		<div id="uploadWrapper" >

		<h3 style="font-size:16px; padding: 3px 0 5px;">Import your online profiles:</h3>

		<form method="post" action="admin.php?page=installation_profiles" id="wpipApiImport">
			<strong>Enter your WPIP username:</strong>&nbsp;&nbsp;<a id="whatsThis" href="http://plugins.ancillaryfactory.com" target="_blank" style="font-size: 10px">Learn more</a><br/><br/>
			<input type="text" name="apiUserName" id="apiUserName" />
			<?php wp_nonce_field('import_from_api','wpip_api'); ?>
			<input type="submit" name="apiSubmit" value="Import" />
			<p style="font-size: 12px;width:300px;line-height: 1.4em"><em>Save your profiles online and import them into your WordPress sites. </em><strong><a href="http://plugins.ancillaryfactory.com" target="_blank" >Create an account</a></strong></p>
		</form>

		</div>
		
<hr style="width:80%;margin:10px auto 30px"/>

<h3 style="font-size:16px;">Import from local file:</h3>
	
	<div id="downloadWrapper">
		<form method="post" action="admin.php?page=installation_profiles" enctype="multipart/form-data" id="importForm">
			<p style="margin-top:0">
				<!-- <strong>Import new profile: </strong><br/> -->
				<input type="file" name="importedFile" />
				<?php wp_nonce_field('upload_profile','wpip_upload'); ?>
				<input type="submit" name="importSubmit" value="Upload" />
			</p>

		</form>

	</div>

	</li> <!-- end Import tab -->

	<li id="export">



		<h3 style="font-size:16px; padding: 3px 0 5px;">Click to download a profile to your computer:</h3>
		<?php  

			if ( wpip_is_windows() ) {
				$pattern = '(txt$)';
			} else {
				$pattern = '(profile$)';
			}

		 ?>


		<ul>

		<?php foreach ($profilesList as $profileFile) { 

			if ( preg_match( $pattern, $profileFile) ) { 
				$nameLength = stripos($profileFile, '.');
				$name = substr($profileFile,0,$nameLength);?>
				<li>
					<a href="plugins.php?page=installation_profiles&download=<?php print esc_attr($profileFile); ?>">
						<?php print $name;?>

					</a>
				</li>	
			<?php }	

		} ?>

		

		<?php 
		$siteName = str_replace(' ', '-', get_bloginfo( 'name' ));
		// $currentSiteProfile = $siteName . '.profile';
		$activePlugins = get_option('active_plugins');
	?>

	

	<p style="margin-top:30px"><a id="choosePluginsButton" style="padding:5px" href="#" class="button">Create a custom profile from this site (<?php print count(get_plugins());?> plugins)</a></p>
		<?php wpip_choose_plugins_to_save(); ?>

	<!-- </div> -->

</li> <!-- end Export tab -->

<div style="clear: both"></div>

		</ul> <!-- end of tabs UL   -->

<!-- </div> -->


	
	</div> <!-- end #wpipFormWrapper -->

</div> <!-- end .wrap -->

<?php } ?>