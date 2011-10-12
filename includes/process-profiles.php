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
		
		$newProfile = fopen(WP_PLUGIN_DIR . '/wpip/profiles/' . $profileName,"w"); 
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

					$('#profileToDownload').attr('href','plugins.php?page=installation_profiles&download=' + filename ).attr('title',filename).text('Download ' + filename);
				}); // end .change
			});
		</script>
	<?php }
	
	
	function wpip_download_profile() {
		$file = trim($_GET['download']);
		$file = WP_PLUGIN_DIR . '/wpip/profiles/' . $file;

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
		$uploadDir = WP_PLUGIN_DIR . '/wpip/profiles/' . $newFileName;
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
				$apiFilename = str_replace(' ', '-', $line);
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
				<?php print '<a href="' . admin_url('plugins.php') . '">Visit plugins page</a>'; ?>
			</p>
			</div>
		<?php } // end if isset 
		
	}