<?php
/*

Plugin Name: Installation Profiles
Plugin URI: 
Description: Download collections of plugins. Go to Plugins -> Installation Profiles
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

if ( isset($_GET['download']) ) {
	add_action('admin_init', 'wpip_download_profile' );
}


if ( isset($_POST['importSubmit'] ) ) {
	add_action('admin_head', 'wpip_import_profile' );
}


if ( isset($_POST['downloadPlugins'] ) ) {
	add_action('admin_head', 'wpip_fetch_plugins' );
}


function wpip_installation_profile_admin() { 

	// read data from default profile
	$readDefaults = fopen(WP_PLUGIN_DIR . '/wpip/profiles/default.profile',"r");
	$defaultLines = fread($readDefaults, filesize(WP_PLUGIN_DIR . '/wpip/profiles/default.profile'));
	fclose($readDefaults);
?>

<div class="wrap"> 
 

 <div id="icon-tools" class="icon32" style="float:left"></div>
<h2>Installation Profiles</h2>

<h4><a href="#" id="wpipToggleHelp">How to add new plugins</a></h4>
<div id="wpipHelp">
	<p>Search the <a href="http://wordpress.org/extend/plugins/" target="_blank">Wordpress Plugin Directory</a> and copy the slug from the plugin's listing page. For example, use the following text for <em>WP Super Cache</em>:</p>
	<img src="<?php print plugins_url('plugin-url.png',__FILE__) ?>"/><br/>
	<p>Plugin names may be added with or without hyphens (e.g. <em>wp-super-cache</em> = <em>wp super cache</em>).</p>
</div> <!-- end help-->
<!--<pre><?php print_r($_POST); ?></pre>-->

<div id="wpipFormWrapper" class="postbox">



<div id="uploadWrapper" >
<h3 style="font-size:16px">Import / Export</h3>
<form method="post" action="admin.php?page=installation_profiles" enctype="multipart/form-data" id="importForm">
	<p style="margin-top:0"><br/>
		<strong>Import new profile: </strong><br/>
		<input type="file" name="importedFile" />
		<input type="submit" name="importSubmit" value="Upload" />
	</p>
</form>

	<div id="downloadWrapper">
		<a class="button-secondary" id="profileToDownload" title="defautl.profile" href="plugins.php?page=installation_profiles&download=default.profile"><strong>Download current profile</strong></a>
	</div>


</div>


<form method="post" action="admin.php?page=installation_profiles" id="profileForm">
		<p>
		
		<strong>Choose a profile:</strong><br/>
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
		</p>
		
		<p><strong>Plugins</strong> <em>(names found in the <a href="http://wordpress.org/extend/plugins/" target="_blank">Wordpress Plugin Directory</a>)</em>:<br/>
			<textarea name="pluginNames" id="pluginNames" rows="15" cols="46"><?php print $defaultLines; ?></textarea>
		</p>
		
		<p style="margin-top: 20px;">
		<input class="button-secondary" type="submit" name="saveProfile" value="Save profile" style="padding:5px"/>&nbsp;&nbsp;
		<input class="button-primary" type="submit" name="downloadPlugins" value="Download plugins and save profile" style="padding:5px" id="downloadPlugins"/>
		</p>
	</form>
	
	
	</div> <!-- end #wpipFormWrapper -->
	
<?php } ?>