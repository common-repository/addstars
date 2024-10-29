<div class="wrap">
<h2><?php esc_html_e($this->plugin->displayName); ?> &raquo; <?php esc_html_e( 'Settings', 'addstars' ); ?></h2>

	<?php
	if ( isset( $this->message ) ) {
		?>
		<div class="updated fade"><p><?php esc_html_e($this->message); ?></p></div>
		<?php
	}
	if ( isset( $this->errorMessage ) ) {
		?>
		<div class="error fade"><p><?php esc_html__($this->errorMessage); ?></p></div>
		<?php
	}
	?>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<!-- Content -->
			<div id="post-body-content">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3 class="hndle">AddStars Settings</h3>
						<div class="inside">
						<form action="options-general.php?page=addstars" method="post">
								<p>
									<label for="ihaf_insert_header"><strong>Paste your AddStars User ID here</strong></label>
									<input name="ihaf_insert_header" id="ihaf_insert_header" style="font-family:Courier New;" ><?php esc_html_e( " (Current Setting = ".$this->settings['ihaf_insert_header'].") "); ?> The AddStars script will be inserted in the Header section <code>&lt;head&gt;</code>
								</p>
									<?php wp_nonce_field( $this->plugin->name, $this->plugin->name . '_nonce' ); ?>
									<p>
										<input name="submit" type="submit" name="Submit" class="button button-primary" value="<?php esc_attr_e( 'Save', 'insert-headers-and-footers' ); ?>" />
									</p>	
							</form>
						</div>
					</div>
					<!-- /postbox -->
				</div>
				<!-- /normal-sortables -->
			</div>
			<!-- /post-body-content -->
		<!-- /postbox-container -->
		</div>
	</div>
</div>
