<?php
/**
 * Notices template
 */
?>
<div class="notice notice-success is-dismissible addstars-notice-welcome">
	<p>
		Thank you for installing AddStars.
		<a href="<?php esc_html_e($setting_page); ?>"><?php esc_html_e( 'Click here', 'addstars' ); ?></a> <?php esc_html_e( 'to configure the plugin.', 'addstars' ); ?>
	</p>
</div>
<script type="text/javascript">
	jQuery(document).ready( function($) {
		$(document).on( 'click', 'addstars-notice-welcome button.notice-dismiss', function( event ) {
			event.preventDefault();
			$.post( ajaxurl, {
				action: '<?php esc_html_e('addstars_dismiss_dashboard_notices'); ?>',
				nonce: '<?php echo wp_create_nonce( 'adddstars-nonce' ); ?>'
			});
			$esc_html_e('addstars-notice-welcome'.remove());
		});
	});
</script>
