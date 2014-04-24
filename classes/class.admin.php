<?php
class SJ2DTAG_admin extends SJ2DTAG_functions {
	public static function admin_enqueue_scripts() {
		if ( $_GET['page'] == self::$text_domain ) {
			$version = (float) substr( get_bloginfo( 'version' ), 0, 3 );

			wp_enqueue_script( 'jquery' );

			if ( $version >= 3.2 ) {
				wp_enqueue_script( 'ui-core-jquery', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/jquery-ui.min.js' );
				wp_enqueue_style( 'ui-core-jquery', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/themes/base/jquery-ui.css' );
			}

			if ( $version >= 3.0 ) {
				wp_enqueue_script( 'spectrum', SJ_2DTAG_PLUGIN_URL . '/assets/spectrum/spectrum.js' );
				wp_enqueue_style( 'spectrum', SJ_2DTAG_PLUGIN_URL . '/assets/spectrum/spectrum.css' );
			}


			wp_enqueue_script( 'sujin_tag', SJ_2DTAG_PLUGIN_URL . '/assets/admin.js' );
			wp_enqueue_style( 'sujin_tag', SJ_2DTAG_PLUGIN_URL . '/assets/admin.css' );

			if ( $version < 3.8 ) {
				wp_enqueue_style( 'sujin_tag_38', SJ_2DTAG_PLUGIN_URL . '/assets/admin-under-38.css' );
			}
		}
	}

	public static function register_admin_menu() {
		add_options_page(
			__( '2D Tag Cloud', self::$text_domain ),
			__( '2D Tag Cloud', self::$text_domain ),
			'manage_options',
			self::$text_domain,
			array( 'SJ2DTAG_admin', 'admin_menu' )
		);
	}

	public static function admin_menu() {
		$set = $_GET['set'];

		if ( isset( $_POST['action'] ) && $_POST['action'] == 'update' && check_admin_referer( self::$text_domain ) ) {
			self::save_settings();
			?><div id="message" class="updated"><p>Save setting successfully!</a></p></div><?php
		}

		if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' && wp_verify_nonce( $_GET['sujin-2d-tag-cloud'], 'delete' ) ) {
			self::delete_settings( $_GET['set'] );
			$set = false;
			?><div id="message" class="updated"><p>Delete setting successfully!</a></p></div><?php
		}

		$options = self::get_option();

		if ( empty( $set ) && !empty( $options ) ) {
			$set = key( $options );
		} else if ( empty( $options ) ) {
			$set = 'new';
		}

		$option = self::get_option( $set );

		$version = (float) substr( get_bloginfo( 'version' ), 0, 3 );

		if ( $version < 3.4 ) {
			include_once( SJ_2DTAG_VIEW_DIR . '/admin-under-3.4.php');
		} else {
			include_once( SJ_2DTAG_VIEW_DIR . '/admin.php');
		}
	}

	private static function delete_settings( $set ) {
		$options = self::get_option();
		unset( $options[$set] );
		update_option( 'SJ_2DTAG_CONFIG', $options );
	}

	private static function save_settings() {
		$id = $_POST['set_current_id'];

		if ( !$_POST['set_name'] ) {
			?><div id="message" class="error"><p>Please Enter the Title!</a></p></div><?php
			return false;
		}

		$data = array(
			'title' => $_POST['set_name'],
			'tag_method' => $_POST['tag_method'],
			'line_height' => $_POST['line_height'],
			'line_height_unit' => $_POST['line_height_unit'],
			'margin_right' => $_POST['margin_right'],
			'margin_bottom' => $_POST['margin_bottom'],
			'underline' => isset( $_POST['underline'] )
		);

		$data['tag_config'] = array();
		foreach( $_POST['color_inp'] as $key => $value ) {
			$data['tag_config'][ $key ] = array(
				'color' => $_POST['color_inp'][$key],
				'bgcolor' => $_POST['bgcolor_inp'][$key],
				'color_over' => $_POST['color_over_inp'][$key],
				'bgcolor_over' => $_POST['bgcolor_over_inp'][$key],
				'radius' => $_POST['radius_inp'][$key],
				'padding' => $_POST['padding_inp'][$key],
				'size' => $_POST['size_inp'][$key]
			);
		}

		$options = get_option( 'SJ_2DTAG_CONFIG' );

		if ( $id == 'new' ) {
			$options[] = $data;
		} else {
			$options[$id] = $data;
		}

		update_option( 'SJ_2DTAG_CONFIG', $options );
	}
}


