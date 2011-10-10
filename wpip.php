<?php
/*

Plugin Name: Installation Profiles
Plugin URI: 
Description: Download collections of plugins
Version: 0.3
Author: Jon Schwab
Author URI: http://www.ancillaryfactory.com
License: GPL2



Copyright 2011    (email : jsschwab@aoa.org)

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


require(WP_PLUGIN_DIR . '/wpip/includes/process-profiles.php');


function wpip_installation_profile_admin_actions() {
	$page = add_submenu_page( 'plugins.php', 'Installation Profiles', 'Installation Profiles', 'install_plugins', 'installation_profiles', 'wpip_installation_profile_admin' );
	
	add_action( 'admin_print_styles-' . $page, 'wpip_admin_styles' );
	add_action('admin_footer-'. $page, 'wpip_profile_select' );
}

function wpip_installation_profile_admin_init() {
       wp_register_style( 'wpipStylesheet', plugins_url('css/wpip.css', __FILE__) );
	   wp_register_script( 'wpipScripts', plugins_url('js/wpip.js', __FILE__),'jquery',false,true );
	   wp_register_script( 'wpipModal', plugins_url('js/jquery.simplemodal.1.4.1.min.js', __FILE__),'jquery',false,true );
   }

function wpip_admin_styles() {
       wp_enqueue_style( 'wpipStylesheet' );
	   wp_enqueue_script( 'wpipScripts' );
	   wp_enqueue_script( 'wpipModal' );
   }

add_action('admin_init', 'wpip_installation_profile_admin_init' );
add_action('admin_menu', 'wpip_installation_profile_admin_actions');



if ( isset($_POST['saveProfile']) || isset($_POST['downloadPlugins']) ) {
	add_action('admin_head', 'wpip_save_profile' );
}


function wpip_installation_profile_admin() { 

	// read data from default profile
	$readDefaults = fopen(WP_PLUGIN_DIR . '/wpip/profiles/default.profile',"r");
	$defaultLines = fread($readDefaults, filesize(WP_PLUGIN_DIR . '/wpip/profiles/default.profile'));
	fclose($readDefaults);
?>

<div class="wrap"> 
  <div id="icon-edit-pages" class="icon32" style="float:left"></div>
<h2>Installation Profiles</h2>

<!--<pre><?php print_r($_POST); ?></pre>-->

<form method="post" action="admin.php?page=installation_profiles" id="profileForm">
		<p>
		
		<strong>Choose:</strong><br/>
		<select id="profileFilename" name="profileFilename">
			<?php 
			$dir = WP_PLUGIN_DIR . '/wpip/profiles';
			$profilesList = scandir($dir);
			foreach ( $profilesList as $profileFile ) {
				if ( preg_match( '(profile$)', $profileFile) ) {
					$nameLength = stripos($profileFile, '.');
					$name = substr($profileFile,0,$nameLength);
					echo '<option value="' . $profileFile . '">' . $name . '</option>';
				}
			}			
		?>
		</select>
		<br/><br/>
		<strong>Or save this profile as:</strong><br/>
			<input type="text" name="profileName" id="profileName" style="width:200px;" placeholder="Name"/>
		</p><br/>
		
		<p><strong>Plugins</strong> <em>(names found in the <a href="http://wordpress.org/extend/plugins/" target="_blank">Wordpress Plugin Directory</a>)</em>:<br/>
			<textarea name="pluginNames" id="pluginNames" rows="15" cols="46"><?php print $defaultLines; ?></textarea>
		</p>
		<input type="submit" name="saveProfile" value="Save profile" style="padding:5px"/>&nbsp;&nbsp;
		<input type="submit" name="downloadPlugins" value="Download plugins" style="padding:5px" id="downloadPlugins"/>
	</form>

	
<?php } ?>