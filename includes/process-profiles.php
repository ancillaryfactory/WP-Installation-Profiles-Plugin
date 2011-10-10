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
			<a href="<?php echo plugins_url('download.php',dirname(__FILE__)); ?>?file=<?php print $profileName ?>">Download</a>
			</p>
		</div>
	<?php }
	}
	
	function wpip_profile_select() { ?>
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