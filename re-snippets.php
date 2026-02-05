<?php
/**
 * Plugin Name: RB Snippets
 * Description: Вывод контента страниц и блоков через шорткоды.
 * Version:     1.0.0
 * Author:      Roman Bolandeu
 * GitHub Plugin URI: https://github.com/vash-akkount/rb-snippets
 */

require 'vendors/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/bolandeu/rb-snippets/',
	__FILE__, // Путь к основному файлу плагина
	'rb-snippets'
);

$myUpdateChecker->getStrategy()->setBranch('main');

// Запрет прямого доступа
if (!defined('ABSPATH')) exit;
 
 // Список функций, раскоментировать для активации
// require_once plugin_dir_path( __FILE__ ) . 'inc/acf-shortcode.php';
// require_once plugin_dir_path( __FILE__ ) . 'inc/shortcodes.php';
// require_once plugin_dir_path( __FILE__ ) . 'inc/rest-api-extensions.php';
// require_once plugin_dir_path( __FILE__ ) . 'inc/smtp.php';
// require_once plugin_dir_path( __FILE__ ) . 'inc/permalink-manager-rest.php';
// require_once plugin_dir_path( __FILE__ ) . 'inc/acf-site-settings.php';
