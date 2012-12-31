<?php 	



	function wpip_save_profile() {

	// checks for form submission

	$lines = ($_POST['pluginNames']);
	$linesArray = explode("\n", $lines);

		if ( wpip_is_windows() ) {

			$validExtension = '.txt';

		} else {

			$validExtension = '.profile';

		}

		// checks for new filename or saves over existing file
		if ( !empty($_POST['profileName']) ) {

			$profileName = esc_attr($_POST['profileName']) . $validExtension;

		} else {

			$profileName = esc_attr($_POST['profileFilename']);

		}

		$profileName = str_replace(' ', '-', $profileName);

		// write file if nonce verifies and filename is valid
		if ( wp_verify_nonce($_POST['wpip_submit'],'plugins_to_download') && !validate_file($profileName)) {	
			
			// initialize Filesystem API
			$url = wp_nonce_url('plugins.php?page=installation_profiles','wpip');
			if ( ! WP_Filesystem($creds) ) {
				request_filesystem_credentials($url, '', true, false, null);
				return;
			}

			global $wp_filesystem; 

			$newProfile = $wp_filesystem->put_contents(
				  WP_PLUGIN_DIR . '/install-profiles/profiles/' . $profileName,
				  $lines,
				  FS_CHMOD_FILE // predefined mode settings for WP files
				);
			
			// $newProfile = fopen(WP_PLUGIN_DIR . '/install-profiles/profiles/' . $profileName,"w"); 
			// $written =  fwrite($newProfile, $lines);
			// fclose($newProfile);

		}

		if ( ($newProfile)  ) { ?>

		<div class="updated below-h2">
			<p><strong><?php print esc_attr($profileName); ?></strong> saved.&nbsp;  
				<a href="plugins.php?page=installation_profiles&download=<?php print $profileName ?>">Download</a>
			</p>
		</div>

		<?php } else { ?>

			<div class="error below-h2">

				<p><strong>There was a problem saving the profile. Please enter a valid filename.</strong></p>

			</div>

		<?php } 

	} // end check for valid filename



	function wpip_profile_select() { 

		// manages the ajax request to load profile files
	?>	



		<script type="text/javascript">

			jQuery(document).ready(function($) {
					$('#profileFilename').change(function() {
					var filename = $(this).val();
					var filepath = '<?php print plugins_url('profiles',dirname(__FILE__)) ?>/' + filename;

					$.ajax({

						url: filepath,

						cache:false,

						success:function(text) {

							$('#pluginNames').val(text);

						}
					});
				}); // end .change
			});

		</script>

	<?php }





function wpip_download_profile() {

		// sanitize filename & path 

		$file = trim(urldecode($_GET['download']));

		$fileExtension = end(explode('.', $file));

		if ( wpip_is_windows() ) {

			$validExtension = 'txt';

		} else {

			$validExtension = 'profile';
		}



		if ( !validate_file($file) && $fileExtension == $validExtension ) {

			$file = WP_PLUGIN_DIR . '/install-profiles/profiles/' . $file;	

			if (file_exists($file)) {

				header('Content-Description: File Transfer');

				header('Content-Type: application/octet-stream');

				header('Content-Disposition: attachment; filename='.basename($file));

				header('Content-Transfer-Encoding: binary');

				header('Expires: 0');

				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

				header('Pragma: public');

				header('Content-Length: ' . filesize($file));

				ob_clean();

				flush();

				readfile($file);

				exit;

			}

	} // end check for valid file

}



	

	function wpip_import_profile() {

		// add check for '.profile' in filename

		$newFile = $_FILES['importedFile']['tmp_name'];
		$newFileName = $_FILES['importedFile']['name'];
		$uploadDir = WP_PLUGIN_DIR . '/install-profiles/profiles/' . $newFileName;


		// check if file ends in .profile
		$fileExtension = end(explode('.', $newFileName));

		if ( wpip_is_windows() ) {
			$validExtension = 'txt';
		} else {
			$validExtension = 'profile';
		}

	if ( $fileExtension == $validExtension ) {
			$extensionValid = true;
		}

		if ( $extensionValid && wp_verify_nonce($_POST['wpip_upload'],'upload_profile') ) {

			$moved = move_uploaded_file($newFile,$uploadDir);

		}


		if ( $moved ) { ?>
			<div class="updated">
				<p>Imported <strong><?php print esc_attr($newFileName); ?></strong>. </p>
			</div>



		<?php }	else { ?>
			<div class="error">
				<p>Couldn't import <strong><?php print esc_attr($newFileName); ?></strong>. </p>
				<p>Valid extension: <?php echo $validExtension;  ?></p>
			</div>
		<?php }

	}


	function wpip_fetch_plugins() {

		$lines = $_POST['pluginNames'];
		$linesArray = explode("\r\n", $lines);

		if ( !empty($lines) && $_POST['downloadPlugins'] && wp_verify_nonce($_POST['wpip_submit'],'plugins_to_download')) { 

			
			// initialize Filesystem API
			$url = wp_nonce_url('plugins.php?page=installation_profiles','wpip');
			if ( ! WP_Filesystem($creds) ) {
				request_filesystem_credentials($url, '', true, false, null);
				return;
			}

			global $wp_filesystem; 
		?>

			<div class="updated below-h2">
			<p><strong>Downloaded plugins:</strong></p>

			<ul id="pluginDownloadSuccess">

			<?php 

			foreach ($linesArray as $line) {
				unset($downloadTest);
				unset($pos);
				$apiFilename = trim(str_replace(' ', '-', $line));
				//$apiFilename = urlencode($apiFilename);
				// check to see if slug starts with http://...
				// if it does, extract the plugin slug

				if ( empty($apiFilename) || $apiFilename == 'install-profiles' ) {
					continue;
				}

				if ( starts_with($apiFilename,'http://wordpress')){
					$apiFilename = rtrim($apiFilename,'/');
					$pos = strrpos($apiFilename, '/');
					$apiFilename = substr($apiFilename, $pos+1);
				}

				$apiURL = 'http://api.wordpress.org/plugins/info/1.0/' . $apiFilename . '.xml';

				$plugin = simplexml_load_file($apiURL);



				// gets filename from Wordpress API

					$pluginURL = $plugin->download_link;
					$apiName = $plugin->name;
					$apiVersion = $plugin->version;
					$apiHomepage = $plugin->homepage;


					if ( !empty($pluginURL) ) {
						$path_parts = pathinfo($pluginURL);
						$filename = $path_parts['filename'] . '.' . $path_parts['extension'];
						$path = WP_PLUGIN_DIR . '/install-profiles/profiles/' . $filename;

						$response = wp_remote_get($pluginURL);
						
						// place downloaded plugin .ZIP in the WPIP folder before unzipping
						$downloadTest = $wp_filesystem->put_contents(
						  $path,
						  $response['body'],
						  FS_CHMOD_FILE // predefined mode settings for WP files
						);

						// extracts and deletes zip file
						$zip = new ZipArchive;
						if ($zip->open($path) === TRUE) {
							$zip->extractTo(WP_PLUGIN_DIR);
							$zip->close();
							//echo 'ok';
						} else {
							//echo 'failed';
						}

					}

					if ( $downloadTest ) {
						$delete = unlink($path);
						print '<li><a href="' . esc_url($apiHomepage) . '" target="_blank">'. esc_attr($apiName) . '</a> ' . esc_attr($apiVersion) . '</li>';
					} else {
						print "<li>Couldn't find <strong>'" . esc_attr($line) . "'</strong></li>";
					}  

			} // end foreach  ?>

			</ul>		
			<p style="margin-top:20px;font-weight:bold">
				<?php print '<a href="' . admin_url('plugins.php?plugin_status=inactive') . '">Visit plugins page</a>'; ?>
			</p>

			</div>
		<?php } // end if isset 


	}

	/////////////////////////////////////////////////////


	function wpip_import_from_wpip_api() {

		$apiUserName = urlencode($_POST['apiUserName']);
		$wpipApiURL = 'http://plugins.ancillaryfactory.com/api/user/'.$apiUserName;
		$apiProfileData = simplexml_load_file($wpipApiURL);

	
		$profileCount = count($apiProfileData->profile);



		if ( wp_verify_nonce($_POST['wpip_api'],'import_from_api')  ) {
			$i = 0;
			
			// initialize Filesystem API
			$url = wp_nonce_url('plugins.php?page=installation_profiles','wpip');
			if ( ! WP_Filesystem($creds) ) {
				request_filesystem_credentials($url, '', true, false, null);
				return;
			}

			global $wp_filesystem;

			while ( $i < $profileCount ) { 

				unset($importedProfilePlugins);

				$importedProfileName = $apiProfileData->profile[$i]->name;

				if ( wpip_is_windows() ) {
					$importedFileName = sanitize_title($importedProfileName) . '.txt';
				} else {
					$importedFileName = $importedProfileName . '.profile';
				}


				$plugins =  $apiProfileData->profile[$i]->plugins->plugin; 

				foreach ( $plugins as $plugin ) { 
					if ( !empty($plugin) ) {
						$importedProfilePlugins .= trim($plugin) . PHP_EOL;
					} 

				} // end foreach

				
				$wp_filesystem->put_contents(
				  WP_PLUGIN_DIR . '/install-profiles/profiles/' . $importedFileName,
				  $importedProfilePlugins,
				  FS_CHMOD_FILE // predefined mode settings for WP files
				);

				// file_put_contents(WP_PLUGIN_DIR . '/install-profiles/profiles/' . $importedFileName,$importedProfilePlugins);	

				$i++;
			}  // end while 
		} // end nonce check	
	?>

		<div class="updated below-h2">
			<p>Imported <?php print $profileCount; ?>
				<?php if ( $profileCount == 1 ) {
					echo ' profile ';
				} else {
					echo ' profiles ';
				} ?>

			from <?php print esc_attr($apiUserName);?>.</p>
		</div>
	<?php } 



function wpip_choose_plugins_to_save() { ?>



	<form method="post" action="" id="pluginCheckboxForm" style="display: none">
	<a href="#" class="simplemodal-close" style="float:right;text-decoration: none;font-weight: bold;color:#000;font-size:16px">X</a>

	<div style="margin-bottom:30px">
		<label class="modalHeadline">Save profile as: </label>
		<input class="largeInput" type="text" name="profileName" value="<?php echo str_replace(' ', '-', get_bloginfo( 'name' ));?>" required="required"/> 
	</div>


	<p><strong>Include the following plugins:</strong>
		<span style="margin-left: 150px"><a class="button" id="wpip_check_all" href="#">Check all</a>&nbsp;&nbsp;<a class="button" href="#" id="wpip_clear_all">Uncheck all</a></span>
	</p>


	<div id="checkboxContainer">

	<?php 

		$i = 0;
		$plugins = get_plugins();
		$slugs = array_keys($plugins);

		foreach ($plugins as $plugin) { 

	     	$slug = array_keys($plugin); 
			$slugPath = $slugs[$i++]; 

	 		// use the folder name as the slug
	 		$arr = explode("/", $slugPath, 2);
	  		$slug = $arr[0]; 

			// no need to add WPIP to a profile!
			if ($slug == 'install-profiles'){continue;}

			// skip over plugins that aren't in folders
			$pos = strpos($slug, '.php');

			if ($pos) {
				continue;
			}

			?>



			<div class="pluginCheckbox">

				<input class="pluginCheckbox" name="currentSlugs[]" type="checkbox" 

					<?php if (is_plugin_active($slugPath)) { ?>
						checked="checked"
					<?php } ?>

					value="<?php echo esc_attr($slug); ?>"/>

				<?php echo esc_attr($plugin['Name']);?>

				<br/>
			</div>

		<?php } ?>

		</div> <!-- end #checkboxContainer-->

	<?php wp_nonce_field('build_custom_profile','wpip_custom'); ?>

	<input name="customProfileSubmit" type="submit" class="button-primary" value="Save and Download" style="float:right"/>

	</form>

<?php }




function wpip_build_custom_profile() {

	$profileName = sanitize_title($_POST['profileName'], get_bloginfo( 'name' ));	

	if ( wpip_is_windows()  ) {
		$profileName = str_replace(' ', '-', $profileName) . '.txt';
	} else {
		$profileName = str_replace(' ', '-', $profileName) . '.profile';
	}

	if (!validate_file($profileName) && wp_verify_nonce($_POST['wpip_custom'],'build_custom_profile')) { // false means the file validates
		$fileContents = '';

		$currentSlugs = esc_attr($_POST['currentSlugs']);

		// assemble the file contents from the $_POST checkbox array
		foreach ($currentSlugs as $slug) {	
			$fileContents .= $slug . PHP_EOL;
		}


		$url = wp_nonce_url('plugins.php?page=installation_profiles','wpip');
		if ( ! WP_Filesystem($creds) ) {
			request_filesystem_credentials($url, '', true, false, null);
			return;
		}

		global $wp_filesystem; 

		$wp_filesystem->put_contents(
			  WP_PLUGIN_DIR . '/install-profiles/profiles/' . $profileName,
			  $fileContents,
			  FS_CHMOD_FILE // predefined mode settings for WP files
			);

		$file = WP_PLUGIN_DIR . '/install-profiles/profiles/' . $profileName;
		$fileContents = '';

		$currentSlugs = $_POST['currentSlugs'];

		// assemble the file contents from the $_POST checkbox array
		foreach ($currentSlugs as $slug) {	
			$fileContents .= $slug . PHP_EOL;
		}


		$wp_filesystem->put_contents(
			  WP_PLUGIN_DIR . '/install-profiles/profiles/' . $profileName,
			  $fileContents,
			  FS_CHMOD_FILE // predefined mode settings for WP files
			);

		// send the file download to the browser

		if (file_exists($file)) {
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename='.basename($file));
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file));

				ob_clean();

				flush();

				readfile($file);

				exit;
			}

	} // end check for validate_file()

}



// Checks for Windows server to change .profile to .txt extension
// IIS will not serve .profile files without extra configuration
function wpip_is_windows() {

	$pos = strpos($_SERVER['SERVER_SOFTWARE'],'Microsoft');	

	if ( $pos === false ) {
		return false;
	} else {
		return true;
	}

}


function starts_with($source, $prefix) {
   return strncmp($source, $prefix, strlen($prefix)) == 0;
}

