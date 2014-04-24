<div id="publish">
	<div class="postbox">
		<h3 class="hndle"><span class="dashicons dashicons-pressthis"></span> <span>Publish</span></h3>
		<div id="major-publishing-actions">
			<?php if ( $set === 0 || $set != 'new' ) { ?>
			<div id="delete-action">
				<a class="submitdelete deletion" href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'set' => $set ) ), 'delete', SJ2DTAG_functions::$text_domain ); ?>">Delete</a>
			</div>
			<?php } ?>

			<div id="publishing-action">
				<button class="button button-primary button-large" id="publish" accesskey="p"><span class="dashicons dashicons-pressthis"></span> Save Setting</button>
			</div>
			<div class="clear"></div>
		</div>
	</div>
</div>
