	<div class="ui-footer">
		<?php 
			$general_opt	=	new ET_GeneralOptions();
			$copyright		=	$general_opt->get_copyright();
			if( $copyright != '') {
				echo '<p><strong>'.$copyright.'</strong></p>';
			}	?>
	</div>
	<script type="text/template" id="template_job">
		<?php echo et_template_mobile_job(); ?>
	</script>
	<script type="text/template" id="template_resume">
		<?php echo et_template_mobile_resume(); ?>
	</script>
	<?php do_action('et_mobile_footer') ?>
</body>
</html>