<?php

class SJ_Widget_TagCloud extends WP_Widget {
	public $widget_id = 'tag_cloud_widget_sujin';
	public $widget_name;
	public $widget_title;

	function __construct() {
		global $sj2DTag;

		$this->widget_id = 'tag_cloud_widget_sujin';
		$this->widget_name = __('2D Tag Cloud Widget by Sujin', $sj2DTag->text_domain);
		$this->widget_title = __('2D Tag Cloud Widget by Sujin', $sj2DTag->text_domain);

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
		global $wpdb, $sj2DTag;

		extract($args, EXTR_SKIP);

		$number = isset($instance['number']) ? $instance['number'] : 20;
		$title = isset($instance['title']) ? $instance['title'] : '';
		$separator = isset($instance['separator']) ? $instance['separator'] : '';
		$sort = isset($instance['sort']) ? $instance['sort'] : 'DESC';
		$set = isset($instance['set_id']) ? $instance['set_id'] : 0;

		$sj2DTag->set_by_number($set);
		$sj2DTag->set_cloud_option($number, $separator, $sort);

		echo $before_widget;
		echo $before_title . apply_filters('widget_title', $title) . $after_title;
		echo $sj2DTag->get_tag_cloud();
		echo $after_widget;
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
		global $sj2DTag;

		$number = isset($instance['number']) ? $instance['number'] : 20;
		$title = isset($instance['title']) ? $instance['title'] : '';
		$separator = isset($instance['separator']) ? $instance['separator'] : '';
		$sort = isset($instance['sort']) ? $instance['sort'] : 'DESC';
		$current_set_num = isset($instance['set_id']) ? $instance['set_id'] : 0;

		$tag_set = get_option('sj_tag_set');
		if (!$tag_set) $tag_set = array(0 => 'Default Set');

		?>

			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', $sj2DTag->text_domain); ?> :</label>
				<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" class="widefat" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('set_id'); ?>"><?php _e('Set', $sj2DTag->text_domain); ?> :</label>
				<select id="<?php echo $this->get_field_id('set_id'); ?>" name="<?php echo $this->get_field_name('set_id'); ?>">

					<?php foreach($tag_set as $key=>$value) { ?>

					<option value="<?php echo $key ?>" <?php if ($key == $current_set_num) echo 'selected="selected"' ?>><?php echo $value ?></option>

					<?php } ?>

				</select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of tags to show', $sj2DTag->text_domain); ?> :</label>
				<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" class="widefat" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('separator'); ?>"><?php _e('Separator', $sj2DTag->text_domain); ?> :</label>
				<input id="<?php echo $this->get_field_id('separator'); ?>" name="<?php echo $this->get_field_name('separator'); ?>" type="text" value="<?php echo $separator; ?>" class="widefat" />
			</p>

			<p>
				<label><?php _e('Sort', $sj2DTag->text_domain); ?> :</label>

				<select name="<?php echo $this->get_field_name('sort'); ?>" class="widefat">
					<option value="DESC" <?php if ($sort == 'DESC') echo 'selected="selected"' ?>><?php _e('Put tags by descending order', $sj2DTag->text_domain); ?></option>
					<option value="intersection" <?php if ($sort == 'intersection') echo 'selected="selected"' ?>><?php _e('Put tags 1 by 1. bigger, smaller, bigger, smaller...', $sj2DTag->text_domain); ?></option>
					<option value="name" <?php if ($sort == 'name') echo 'selected="selected"' ?>><?php _e('Sort by name', $sj2DTag->text_domain); ?></option>
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