<?php

class SJ_Widget_TagCloud extends WP_Widget {
	public $widget_id = 'tag_cloud_widget_sujin';
	public $widget_name;
	public $widget_title;

	function __construct() {
		$this->widget_id = 'tag_cloud_widget_sujin';
		$this->widget_name = '2D Tag Cloud Widget by Sujin';
		$this->widget_title = '2D Tag Cloud Widget by Sujin';

		$widget_ops = array(
			'classname' => $this->widget_id,
			'description' =>'Generate 2-Dimentional Tag Cloud'
		);

		$control_ops = array(
			'width' => 500,
		);

		parent::__construct($this->widget_id, $this->widget_name, $widget_ops, $control_ops);
		$this->alt_option_name = 'widget_'.$this->id_base;
	}

	function widget($args, $instance) {
		global $wpdb;

		extract($args, EXTR_SKIP);

		$number = isset($instance['number']) ? $instance['number'] : 20;
		$title = isset($instance['title']) ? $instance['title'] : '';
		$separator = isset($instance['separator']) ? $instance['separator'] : '';
		$sort = isset($instance['sort']) ? $instance['sort'] : 'DESC';
		$set = isset($instance['set_id']) ? $instance['set_id'] : 0;

		echo $before_widget;
		echo $before_title . apply_filters('widget_title', $title) . $after_title;

		$tags_out = sjGetTags($number, $separator, $sort, $set);

		echo '<div class="tag_cloud sj_tagcloud_set_' . $set . '">' . $tags_out . '</div>';
		echo $after_widget;

		echo '<style>' . sjPrintCSS($set) . '</style>';
	} // function widget($args, $instance)

	function update($new_instance, $old_instance) {
		$instance = $old_instance;

		$instance['number'] = $new_instance['number'];
		$instance['title'] = $new_instance['title'];
		$instance['separator'] = $new_instance['separator'];
		$instance['sort'] = $new_instance['sort'];
		$instance['set_id'] = $new_instance['set_id'];

		return $instance;
	} // function update($new_instance, $old_instance)

	function form($instance) {
		$number = isset($instance['number']) ? $instance['number'] : 20;
		$title = isset($instance['title']) ? $instance['title'] : '';
		$separator = isset($instance['separator']) ? $instance['separator'] : '';
		$sort = isset($instance['sort']) ? $instance['sort'] : 'DESC';
		$current_set_num = isset($instance['set_id']) ? $instance['set_id'] : 0;

		$tag_set = get_option('sj_tag_set');
		if (!$tag_set) $tag_set = array(0 => 'Default Set');

		?>

			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">Title :</label>
				<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" class="widefat" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('set_id'); ?>">Set :</label>
				<select id="<?php echo $this->get_field_id('set_id'); ?>" name="<?php echo $this->get_field_name('set_id'); ?>">

					<?php foreach($tag_set as $key=>$value) { ?>

					<option value="<?php echo $key ?>" <?php if ($key == $current_set_num) echo 'selected="selected"' ?>><?php echo $value ?></option>

					<?php } ?>

				</select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('number'); ?>">Number of tags to show :</label>
				<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" class="widefat" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('separator'); ?>">Separator :</label>
				<input id="<?php echo $this->get_field_id('separator'); ?>" name="<?php echo $this->get_field_name('separator'); ?>" type="text" value="<?php echo $separator; ?>" class="widefat" />
			</p>

			<p>
				<label>Sort :</label>

				<select name="<?php echo $this->get_field_name('sort'); ?>" class="widefat">
					<option value="DESC" <?php if ($sort == 'DESC') echo 'selected="selected"' ?>>Put tags by descending order</option>
					<option value="intersection" <?php if ($sort == 'intersection') echo 'selected="selected"' ?>>Put tags 1 by 1. bigger, smaller, bigger, smaller...</option>
					<option value="name" <?php if ($sort == 'name') echo 'selected="selected"' ?>>Sort by name</option>
				</select>
			</p>

		<?php
	} // function form($instance)
}

# Activate the Widget
function sjActivateWidgetTagCloud() {
	register_widget('SJ_Widget_TagCloud');
}
add_action('widgets_init', 'sjActivateWidgetTagCloud');