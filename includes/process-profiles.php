<?php 
	if ( isset($_POST['importSubmit'] ) ) {
		$newFile = $_FILES['importedFile']['tmp_name'];
		$uploadDir = WP_PLUGIN_DIR . '/wpip/profiles/' . $_FILES['importedFile']['name'];
		$moved = move_uploaded_file($newFile,$uploadDir);
	}
	
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

					$('#profileToDownload').attr('href','download.php?file=' + filename ).attr('title',filename);
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