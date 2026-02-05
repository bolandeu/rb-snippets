<?php
/**
 * Plugin Name: RB Snippets
 * Description: Вывод контента страниц и блоков через шорткоды.
 * Version:     1.0.1
 * Author:      Roman Bolandeu
 * GitHub Plugin URI: https://github.com/bolandeu/rb-snippets
 */

if (!defined('ABSPATH')) exit;

// 1. АВТООБНОВЛЕНИЕ (Библиотека должна лежать в папке vendor)
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

if (file_exists(plugin_dir_path(__FILE__) . 'vendors/plugin-update-checker/plugin-update-checker.php')) {
    require_once plugin_dir_path(__FILE__) . 'vendors/plugin-update-checker/plugin-update-checker.php';

    $myUpdateChecker = PucFactory::buildUpdateChecker(
        'https://github.com/bolandeu/rb-snippets/',
        __FILE__,
        'rb-snippets'
    );
    $myUpdateChecker->getStrategy()->setBranch('main');
}

// 2. Запрет прямого доступа
if (!defined('ABSPATH')) exit;
 
// Список функций, раскоментировать для активации

require_once plugin_dir_path( __FILE__ ) . 'inc/shortcodes.php';

 // require_once plugin_dir_path( __FILE__ ) . 'inc/acf-shortcode.php';
// require_once plugin_dir_path( __FILE__ ) . 'inc/rest-api-extensions.php';
// require_once plugin_dir_path( __FILE__ ) . 'inc/smtp.php';
// require_once plugin_dir_path( __FILE__ ) . 'inc/permalink-manager-rest.php';
// require_once plugin_dir_path( __FILE__ ) . 'inc/acf-site-settings.php';


// 3. СТРАНИЦА ПОМОЩИ В АДМИНКЕ
add_action('admin_menu', function() {
    add_options_page(
        'RB Snippets Help',
        'RB Snippets',
        'manage_options',
        'rb-snippets-help',
        'rb_snippets_help_page_html'
    );
});

function rb_snippets_help_page_html() {
    ?>
    <div class="wrap">
        <h1>Справка по шool-кодам RB Snippets</h1>
        <p>Используйте шорткод <code>[page_content]</code> для вставки содержимого одной страницы в другую.</p>
        
        <table class="widefat fixed" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th style="width: 30%;">Пример</th>
                    <th>Описание</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>[page_content id="12"]</code></td>
                    <td>Выводит <strong>весь</strong> контент страницы с ID 12.</td>
                </tr>
                <tr>
                    <td><code>[page_content slug="contact"]</code></td>
                    <td>Выводит контент по <strong>ярлыку</strong> (slug) страницы.</td>
                </tr>
                <tr>
                    <td><code>[page_content id="12" block="1"]</code></td>
                    <td>Выводит только <strong>первый блок</strong> (верхнего уровня).</td>
                </tr>
                <tr>
                    <td><code>[page_content id="12" block="2-4"]</code></td>
                    <td>Выводит <strong>диапазон блоков</strong> со 2-го по 4-й.</td>
                </tr>
            </tbody>
        </table>
        
        <p style="margin-top: 20px;"><em>Примечание: Код автоматически игнорирует пустые блоки и переносы строк.</em></p>
    </div>
    <?php
}