<?php 
	
	
	function wpip_save_profile() {
	// checks for form submission
	$lines = $_POST['pluginNames'];
	$linesArray = explode("\n", $lines);
		
		// checks for new filename or saves over existing file
		if ( !empty($_POST['profileName']) ) {
			$profileName = $_POST['profileName'] . '.profile';
		} else {
			$profileName = $_POST['profileFilename'];
		}
		
		$profileName = str_replace(' ', '-', $profileName);
		
		$newProfile = fopen(WP_PLUGIN_DIR . '/install-profiles/profiles/' . $profileName,"w"); 
		$written =  fwrite($newProfile, $lines);

		fclose($newProfile);
	
	if ( ($written > 0) && !isset($_POST['downloadPlugins']) ) { ?>
		<div class="updated">
			<p><strong><?php print $profileName; ?></strong> saved.&nbsp;  
			<a href="plugins.php?page=installation_profiles&download=<?php print $profileName ?>">Download</a>
			</p>
		</div>
	<?php }
	}
	
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
		$file = trim($_GET['download']);
		$file = WP_PLUGIN_DIR . '/install-profiles/profiles/' . $file;

		// check for request of the current site's profile
		if ( isset($_GET['current']) ) {
				
			// build filename for current site profile
			$siteName = str_replace(' ', '-', get_bloginfo( 'name' ));
			$currentSiteProfileFilename = $siteName . '.profile';
			
			$activePlugins = get_option('active_plugins');
			
			foreach ( $activePlugins as $pluginPath ) {
				$pluginName = dirname($pluginPath);
				if ( $pluginName == '.' ) { // ignore plugins that aren't in a folder
					continue;
				}
				$currentSiteProfile .= $pluginName . PHP_EOL;
			}
			
			$newProfile = fopen(WP_PLUGIN_DIR . '/install-profiles/profiles/' . $currentSiteProfileFilename,"w"); 
			$written =  fwrite($newProfile, $currentSiteProfile);

			fclose($newProfile);
	
		}
		
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
	}
	
	function wpip_import_profile() {
		
		// add check for '.profile' in filename
		$newFile = $_FILES['importedFile']['tmp_name'];
		$newFileName = $_FILES['importedFile']['name'];
		$uploadDir = WP_PLUGIN_DIR . '/install-profiles/profiles/' . $newFileName;
		$moved = move_uploaded_file($newFile,$uploadDir);
		
		if ( $moved ) { ?>
			<div class="updated">
				<p>Imported <strong><?php print $newFileName; ?></strong>. </p>
			</div>
		<?php }	
	}
	
	
	function wpip_fetch_plugins() {
		$lines = $_POST['pluginNames'];
		$linesArray = explode("\n", $lines);
		
		
		if ( !empty($lines) && $_POST['downloadPlugins'] ) { ?>
			<div class="updated">
			<p><strong>Downloaded plugins:</strong></p>
			<ul id="pluginDownloadSuccess">
			<?php 
			foreach ($linesArray as $line) {
				unset($downloadTest);
				$apiFilename = trim(str_replace(' ', '-', $line));
				
				if ( empty($apiFilename) || $apiFilename == 'install-profiles' ) {
					continue;
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
						$path = $filename;
					
						$ch = curl_init($pluginURL);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					 
						$data = curl_exec($ch);
					 
						curl_close($ch);
					 
						$downloadTest = file_put_contents($path, $data);

						// extracts and deletes zip file
						$zip = new ZipArchive;
							
						if ($zip->open($filename) === TRUE) {
							$zip->extractTo(WP_PLUGIN_DIR);
							$zip->close();
							//echo 'ok';
						} else {
							//echo 'failed';
						}
					}
					
					if ( $downloadTest > 0 ) {
						$delete = unlink($filename);
						print '<li><a href="' . $apiHomepage . '" target="_blank">'. $apiName . '</a> ' . $apiVersion . '</li>';
					} else {
						print "<li>Couldn't find <strong>'" . $line . "'</strong></li>";
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
		$apiUserName = mysql_real_escape_string($_POST['apiUserName']);
		
		$wpipApiURL = 'http://plugins.ancillaryfactory.com/api/user/'.$apiUserName;
		$apiProfileData = simplexml_load_file($wpipApiURL);
		
		$profileCount = count($apiProfileData->profile);
		
		$i = 0;
		while ( $i < $profileCount ) { 
			unset($importedProfilePlugins);
			
			$importedProfileName = $apiProfileData->profile[$i]->name;
			$importedFileName = $importedProfileName . '.profile';
		
			$plugins =  $apiProfileData->profile[$i]->plugins->plugin; 
			foreach ( $plugins as $plugin ) { 
				if ( !empty($plugin) ) {
					$importedProfilePlugins .= trim($plugin) . PHP_EOL;
				} 
			} // end foreach
		
			file_put_contents(WP_PLUGIN_DIR . '/install-profiles/profiles/' . $importedFileName,$importedProfilePlugins);	
			$i++;
		}  // end while ?>
		
		<div class="updated">
			<p>Imported <?php print $profileCount; ?>
				<?php if ( $profileCount == 1 ) {
					echo ' profile ';
				} else {
					echo ' profiles ';
				} ?>
			from <?php print $apiUserName;?>.</p>
		</div>
	<?php } 


function wpip_choose_plugins_to_save() { ?>
	<form method="post" action="" id="pluginCheckboxForm" style="display: none">
	
	<div style="margin-bottom:30px">
		<label class="modalHeadline">Save profile as: </label>
		<input class="largeInput" type="text" name="profileName" value="<?php echo get_bloginfo( 'name' );?> "/> 
	</div>
	<p><strong>Include the following plugins:</strong></p>
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
					value="<?php echo $slug; ?>"/>
				<?php echo $plugin['Name'];?>
				<br/>
			</div>
		<?php } ?>
	
		</div> <!-- end #checkboxContainer-->

	<input type="submit" class="button-primary" value="Save amd Download" style="float:right"/>
	</form>
<?php }

/* 
 * add jQuery to show form in modal
 * add function to process checkbox form
 * 
*/
