<?php
/**
 * Plugin Name: 2D Tag Cloud by Sujin
 * Plugin URI: http://www.sujinc.com/2d-tag-cloud-widget/
 * Description: This plugin is one of the WordPress widget, which makes tag-cloud with two visual value. 두가자의 기준에 의해 글자의 색상과 크기를 달리해서 태그를 표시하는 플러그인입니다.
 * Version: 2.8
 * Author: Sujin 수진 Choi 최
 * Author URI: http://www.sujinc.com/
 * License: GPLv2 or later
 * Text Domain: 2d-tag-cloud-widget-by-sujin
 */

global $sj_tag_db_version;
$sj_tag_db_version = "1.0";

require_once('functions.php');
require_once('widget.php');
require_once('shorttag.php');