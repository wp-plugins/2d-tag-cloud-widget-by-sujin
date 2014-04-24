<div id="SJ2DTAG_tags" class="postbox">
	<h3 class="hndle"><span class="dashicons dashicons-tag"></span> <span><?php _e( 'Tags', SJ2DTAG_functions::$text_domain ); ?></span></h3>

	<div class="inside">
		<table id="SJ2DTAG_tag_config" class="form-table">
			<thead>
				<tr>
					<td></td>
					<?php foreach( $option['tag_config'] as $key => $tag_config ) { ?>
					<th id="tag_step_<?php echo $key ?>_preview">
						<span data-step="<?php echo $key ?>"><?php _e( 'Step', SJ2DTAG_functions::$text_domain ); ?> <?php echo $key ?></span> <a class="delete_column"><span class="dashicons dashicons-dismiss"></a></button>
					</th>
					<?php } ?>
				</tr>
			</thead>

			<tbody>
				<!-- 글자 색상 -->
				<tr>
					<th scope="row"><label><?php _e( 'Text Color', SJ2DTAG_functions::$text_domain ); ?></label></th>
					<?php foreach( $option['tag_config'] as $key => $tag_config ) { ?>
					<td>
						<input type="text" id="color_inp_<?php echo $key ?>" name="color_inp[<?php echo $key ?>]" class="color_picker" value="<?php echo $tag_config['color'] ?>" data-step="<?php echo $key ?>" data-key="color" />
					</td>
					<?php } ?>
				</tr>

				<!-- 배경 색상 -->
				<tr>
					<th scope="row"><label><?php _e( 'Background Color', SJ2DTAG_functions::$text_domain ); ?></label></th>
					<?php foreach( $option['tag_config'] as $key => $tag_config ) { ?>
					<td>
						<input type="text" id="bgcolor_inp_<?php echo $key ?>" name="bgcolor_inp[<?php echo $key ?>]" class="color_picker" value="<?php echo $tag_config['bgcolor'] ?>" data-step="<?php echo $key ?>" data-key="bgcolor" />
					</td>
					<?php } ?>
				</tr>

				<!-- 배경 라운딩 -->
				<tr>
					<th scope="row"><label><?php _e( 'Border Radius', SJ2DTAG_functions::$text_domain ); ?></label></th>
					<?php foreach( $option['tag_config'] as $key => $tag_config ) { ?>
					<td>
						<input type="text" id="radius_inp_<?php echo $key ?>" name="radius_inp[<?php echo $key ?>]" class="jquery-spinner" value="<?php echo $tag_config['radius'] ?>" data-step="<?php echo $key ?>" data-key="radius" />
					</td>
					<?php } ?>
				</tr>

				<!-- 글자와 배경 띄우기 -->
				<tr>
					<th scope="row"><label><?php _e( 'Padding', SJ2DTAG_functions::$text_domain ); ?></label></th>
					<?php foreach( $option['tag_config'] as $key => $tag_config ) { ?>
					<td>
						<input type="text" id="padding_inp_<?php echo $key ?>" name="padding_inp[<?php echo $key ?>]" class="jquery-spinner" value="<?php echo $tag_config['padding'] ?>" data-step="<?php echo $key ?>" data-key="padding" />
					</td>
					<?php } ?>
				</tr>

				<!-- 글자 크기 -->
				<tr>
					<th scope="row"><label><?php _e( 'Size', SJ2DTAG_functions::$text_domain ); ?></label></th>
					<?php foreach( $option['tag_config'] as $key => $tag_config ) { ?>
					<td>
						<input type="text" id="size_inp_<?php echo $key ?>" name="size_inp[<?php echo $key ?>]" class="jquery-spinner" value="<?php echo $tag_config['size'] ?>" data-step="<?php echo $key ?>" data-key="size" />
					</td>
					<?php } ?>
				</tr>

				<!-- 글자 색상 (오버 시) -->
				<tr>
					<th scope="row"><label><?php _e( 'Text Color', SJ2DTAG_functions::$text_domain ); ?> <?php _e( '(Over)', SJ2DTAG_functions::$text_domain ); ?></label></th>
					<?php foreach( $option['tag_config'] as $key => $tag_config ) { ?>
					<td>
						<input type="text" id="color_over_inp_<?php echo $key ?>" name="color_over_inp[<?php echo $key ?>]" class="color_picker" value="<?php echo $tag_config['color_over'] ?>" data-step="<?php echo $key ?>" data-key="color_over" />
					</td>
					<?php } ?>
				</tr>

				<!-- 배경 색상 (오버 시) -->
				<tr>
					<th scope="row"><label><?php _e( 'Background Color', SJ2DTAG_functions::$text_domain ); ?> <?php _e( '(Over)', SJ2DTAG_functions::$text_domain ); ?></label></th>
					<?php foreach( $option['tag_config'] as $key => $tag_config ) { ?>
					<td>
						<input type="text" id="bgcolor_over_inp_<?php echo $key ?>" name="bgcolor_over_inp[<?php echo $key ?>]" class="color_picker" value="<?php echo $tag_config['bgcolor_over'] ?>" data-step="<?php echo $key ?>" data-key="bgcolor_over" />
					</td>
					<?php } ?>
				</tr>
			</tbody>
		</table>

		<div class="clear"></div>
	</div>
	<div id="major-publishing-actions" class="buttons">
		<button class="button button-primary button-large" id="add_step_next"><span class="dashicons dashicons-plus-alt"></span> Add Step After</button>
		<button class="button button-primary button-large" id="add_step_prev"><span class="dashicons dashicons-plus-alt"></span> Add Step Before</button>
	</div>
</div>
