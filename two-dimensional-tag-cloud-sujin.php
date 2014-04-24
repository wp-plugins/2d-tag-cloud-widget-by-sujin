<?php
/**
 * Plugin Name: 2D Tag Cloud by Sujin
 * Plugin URI: http://www.sujinc.com/gallery/2d-tag-cloud-widget/
 * Description: Make your tag cloud with two visual value; Your tags will have variety colors and sizes based on their click counts and how many posts have the tags. 두가자의 기준에 의해 글자의 색상과 크기를 달리해서 태그를 표시하는 플러그인입니다.
 * Version: 5.0
 * Author: Sujin 수진 Choi
 * Author URI: http://www.sujinc.com/
 * License: GPLv2 or later
 * Text Domain: sujin-2d-tag-cloud
 */

# 상수 할당
if ( !defined( 'SJ_2DTAG_PLUGIN_NAME' ) ) {
	$basename = trim( dirname( plugin_basename( __FILE__ ) ), '/' );
	if ( !is_dir( WP_PLUGIN_DIR . '/' . $basename ) ) {
		$basename = explode( '/', $basename );
		$basename = array_pop( $basename );
	}

	define( 'SJ_2DTAG_PLUGIN_NAME', $basename );
}


if ( !defined( 'SJ_2DTAG_PLUGIN_DIR' ) )
	define( 'SJ_2DTAG_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . SJ_2DTAG_PLUGIN_NAME );

if ( !defined( 'SJ_2DTAG_PLUGIN_URL' ) )
	define( 'SJ_2DTAG_PLUGIN_URL', WP_PLUGIN_URL . '/' . SJ_2DTAG_PLUGIN_NAME );

if ( !defined( 'SJ_2DTAG_CLASS_DIR' ) )
	define( 'SJ_2DTAG_CLASS_DIR', SJ_2DTAG_PLUGIN_DIR . '/classes' );

if ( !defined( 'SJ_2DTAG_VIEW_DIR' ) )
	define( 'SJ_2DTAG_VIEW_DIR', SJ_2DTAG_PLUGIN_DIR . '/views' );

if ( !defined( 'SJ_2DTAG_VERSION_KEY' ) )
    define( 'SJ_2DTAG_VERSION_KEY', 'SJ_2DTAG_version' );

if ( !defined( 'SJ_2DTAG_VERSION_NUM' ) )
    define( 'SJ_2DTAG_VERSION_NUM', '5.0' );

# 인클루딩
include_once( SJ_2DTAG_CLASS_DIR . '/class.functions.php');
include_once( SJ_2DTAG_CLASS_DIR . '/class.admin.php');
include_once( SJ_2DTAG_CLASS_DIR . '/class.widget.php');
include_once( SJ_2DTAG_CLASS_DIR . '/functions.shorttag.php');

# 이전 버전에서 업그레이드
$previous_version = get_option( 'sj_tag_db_version' );
$version = get_option( SJ_2DTAG_VERSION_KEY );

if ( !empty( $previous_version ) || !$version )
	SJ2DTAG_functions::upgrade_from_under_5();

# 훅
register_activation_hook( SJ_2DTAG_PLUGIN_DIR . '/' . basename(__FILE__) , array( 'SJ2DTAG_functions', 'activate_plugin' ) ); // 활성화
add_action( 'plugins_loaded', array( 'SJ2DTAG_functions', 'load_plugin_textdomain' ) ); // 텍스트도메인
add_filter( 'plugin_action_links_' . SJ_2DTAG_PLUGIN_NAME . '/' . basename(__FILE__), array( 'SJ2DTAG_functions', 'action_links' ), 10, 2 ); // 액션링크

add_action( 'admin_enqueue_scripts', array( 'SJ2DTAG_admin', 'admin_enqueue_scripts' ), 10, 2 ); // 어드민 스크립트
add_action( 'admin_menu', array( 'SJ2DTAG_admin', 'register_admin_menu' ) ); // 어드민 메뉴

add_action( 'widgets_init', 'SJ2DTAG_activate_widget' );

add_action( 'wp', array( 'SJ2DTAG_functions', 'increase_count' ) );
add_shortcode('tag2d', 'SJ2DTAG_shortcode');


function p( $arr ) {
	echo '<pre>';
	print_r( $arr );
	echo '</pre>';
}




























































