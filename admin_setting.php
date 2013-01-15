<?php
# Admin Page
function sj2DTagAddSettingPage() {
	add_options_page('2D Tag Cloud', '2D Tag Cloud', 'manage_options', '2D-tag-cloud-options', 'sj2DTagSetting');
}
add_action('admin_menu', 'sj2DTagAddSettingPage');

function sj2DTagSetting() {
	if ($_POST['action'] == 'update' && check_admin_referer('sj-admin-tag')) {
		$tag_step = $_POST['tag_step'];
		$tag_method = $_POST['tag_method'];
		$setting_method = $_POST['setting_method'];

		$line_height = $_POST['line_height'];
		$line_height_unit = $_POST['line_height_unit'];
		$margin_right = $_POST['margin_right'];
		$margin_bottom = $_POST['margin_bottom'];

		$tag_config = array();

		for($i = 1; $i < $_POST['tag_step']+1; $i++) {
			$tag_config['color'][$i] = array(
				'color' => $_POST['tag_color_step_' . $i],
				'bgcolor' => $_POST['tag_bgcolor_step_' . $i],
				'radius' => $_POST['tag_radius_step_' . $i],
				'padding' => $_POST['tag_padding_step_' . $i]
			);

			$tag_config['size'][$i] =$_POST['tag_size_step_' . $i];
		}
		
		$tag_config = array(
			'tag_step' => $tag_step,
			'tag_method' => $tag_method,
			'tag_config' => $tag_config,
			'setting_method' => $setting_method,
			'line_height' => $line_height,
			'line_height_unit' => $line_height_unit,
			'margin_right' => $margin_right,
			'margin_bottom' => $margin_bottom,
		);
		update_option('sj_tag_conifg', $tag_config);
	}
	
	$tag_config = get_option('sj_tag_conifg');

	# initialize;;
	$config = sjParseOptions($tag_config);
	$tag_config = $config['tag_config'];
	extract($config, EXTR_SKIP);

	if (!$line_height) $line_height = 1.3;
	if (!$line_height_unit) $line_height_unit = 'em';
	if (!$margin_right) $margin_right = 5;
	if (!$margin_bottom) $margin_bottom = 10;

	?>

	<div class="wrap sjTag">
		<div class="icon32" id="icon-options-general"><br></div><h2>2D Tag Cloud Setting</h2>

		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" class="donation">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>

		<form method="post">
			<input type="hidden" value="2D-tag-cloud-options" name="option_page">
			<input type="hidden" value="update" name="action">
			<?php wp_nonce_field('sj-admin-tag') ?>

			<div class="col_wrapper">
				<label for="tag_step">Tag Step</label>
				<input id="tag_step" class="jquery-spinner" name="tag_step" value="<?php echo $tag_step ?>" />
				<p class="desc label"></p>
			</div>
	
			<div class="col_wrapper">
				<label for="tag_method">Output</label>
				<select id="tag_method" name="tag_method">
					<option value="click-color" <?php if ($tag_method == 'click-color') echo 'selected="selected"' ?>>Color:View / Size:Including</option>
					<option value="include-color" <?php if ($tag_method == 'include-color') echo 'selected="selected"' ?>>Color:Including / Size:View</option>
				</select>
				<p class="desc label"></p>
			</div>

			<div class="col_wrapper">
				<label>Preset</label>
				<a href="#" onclick="do_preset_4_white(); return false;">4 Step / Bright Background</a><br />
				<a href="#" onclick="do_preset_4_black(); return false;">4 Step / Dark Background</a>
				<p class="desc label"></p>
			</div>

			<div class="col_wrapper">
				<label for="line_height">Line Height</label>
				<input id="line_height" class="" name="line_height" value="<?php echo $line_height ?>" />
				<select id="line_height_unit" name="line_height_unit">
					<option value="em" <?php if ($line_height_unit == 'em') echo 'selected="selected"' ?>>em</option>
					<option value="px" <?php if ($line_height_unit == 'px') echo 'selected="selected"' ?>>px</option>
				</select>
				<p class="desc label"></p>
			</div>

			<div class="col_wrapper">
				<label for="margin_right">Right Margin</label>
				<input id="margin_right" class="jquery-spinner" name="margin_right" value="<?php echo $margin_right ?>" />
				<p class="desc label"></p>
			</div>

			<div class="col_wrapper">
				<label for="margin_bottom">Bottom Margin</label>
				<input id="margin_bottom" class="jquery-spinner" name="margin_bottom" value="<?php echo $margin_bottom ?>" />
				<p class="desc label"></p>
			</div>

			<div id="prev_wrapper">
				<table id="sjTagTable" class="wp-list-table widefat fixed posts">
					<thead>
						<tr>
							<th>&nbsp;</th>
							<?php foreach($tag_config['color'] as $key => $value) { ?>
							<th id="tag_step_<?php echo $key ?>_preview"><span>Step <?php echo $key ?></span></th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<tr>
							<th>Text Color</th>
							<?php foreach($tag_config['color'] as $key => $value) { ?>
							<td><input type="text" id="tag_color_step_<?php echo $key ?>" name="tag_color_step_<?php echo $key ?>" class="tag_color color-picker" value="<?php echo $value['color'] ?>" /></td>
							<?php } ?>
						</tr>

						<tr>
							<th>Background Color</th>
							<?php foreach($tag_config['color'] as $key => $value) { ?>
							<td><input type="text" id="tag_bgcolor_step_<?php echo $key ?>" name="tag_bgcolor_step_<?php echo $key ?>" class="tag_bgcolor color-picker" value="<?php echo $value['bgcolor'] ?>" /></td>
							<?php } ?>
						</tr>

						<tr>
							<th>Border Radius</th>
							<?php foreach($tag_config['color'] as $key => $value) { ?>
							<td><input type="text" id="tag_radius_step_<?php echo $key ?>" name="tag_radius_step_<?php echo $key ?>" class="tag_radius jquery-spinner" value="<?php echo $value['radius'] ?>" /></td>
							<?php } ?>
						</tr>

						<tr>
							<th>Padding</th>
							<?php foreach($tag_config['color'] as $key => $value) { ?>
							<td><input type="text" id="tag_padding_step_<?php echo $key ?>" name="tag_padding_step_<?php echo $key ?>" class="tag_padding jquery-spinner" value="<?php echo $value['padding'] ?>" /></td>
							<?php } ?>
						</tr>

						<tr>
							<th>Size</th>
							<?php foreach($tag_config['size'] as $key => $value) { ?>
							<td><input type="text" id="tag_size_step_<?php echo $key ?>" name="tag_size_step_<?php echo $key ?>" class="tag_size jquery-spinner" value="<?php echo $value ?>" /></td>
							<?php } ?>
						</tr>
					</tbody>
				</table>
				
				<h3 id="sjTagH3Preview">Preview <a href="#" onclick="sjSetPreview(); return false;" class="button">Make Preview</a></h3>
				<div id="sjTagPreview">
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Tag</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Cloud</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Wordpress</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">API</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">PHP</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">CMS</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Linux</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">한국어</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">English</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">日本語</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">le français</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Community</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Europe</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">North and South America</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Asia</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Africa</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Oceania</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Information</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Languages</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Italy</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Canada</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Haiti</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Brazil</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Egypt</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Southeast</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Lebanon</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Situation</a>, 
					<a style="display:inline-block; text-decoration:none;" href="#" onclick="return false;">Phonology</a>, 
				</div>
			</div>

			<p class="submit">
				<input type="submit" value="Save Changes" class="button button-primary" id="submit" name="submit">
				<a href="<?php echo $_SERVER['REQUEST_URI'] ?>" class="button">Cancel</a>
			</p>
		</form>
	</div>
	<?php
}