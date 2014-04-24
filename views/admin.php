<div id="SJ2DTAG" class="wrap">
	<?php include_once( SJ_2DTAG_VIEW_DIR . '/admin-tabs.php'); ?>

	<?php if ( empty( $options ) ) : ?>
	<!-- 에러 메시지 -->
	<br class="clear">
	<div id="message" class="error"><p>You don't have any option. Make new one now :)</a></p></div>
	<?php endif; ?>

	<form method="post" id="SJ2DTAG_Form">
		<input type="hidden" value="2D-tag-cloud-options" name="option_page">
		<input type="hidden" value="update" name="action">
		<input type="hidden" value="<?php echo $set ?>" name="set_current_id">

		<?php wp_nonce_field( SJ2DTAG_functions::$text_domain ) ?>

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
					<div id="titlediv">
						<div id="titlewrap">
							<input type="text" name="set_name" size="30" value="<?php echo $option['title'] ?>" id="title" autocomplete="off">
						</div>
					</div>
				</div>

				<div id="postbox-container-1" class="postbox-container inner-sidebar">
					<?php include_once( SJ_2DTAG_VIEW_DIR . '/metabox-donation.php'); ?>
					<?php include_once( SJ_2DTAG_VIEW_DIR . '/metabox-publish.php'); ?>
					<?php include_once( SJ_2DTAG_VIEW_DIR . '/metabox-preview.php'); ?>
				</div>

				<div id="postbox-container-2" class="postbox-container meta-box-sortables">
					<?php include_once( SJ_2DTAG_VIEW_DIR . '/metabox-options.php'); ?>
					<?php include_once( SJ_2DTAG_VIEW_DIR . '/metabox-colors.php'); ?>
				</div>
			</div>
		</div>
	</form>
</div>















