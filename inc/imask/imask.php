<?php
/**
 * RB Snippets - IMask Phone Input
 *
 * Маска для полей ввода телефона
 * Применяется ко всем элементам с классом .phone-input
 */

if (!defined('ABSPATH')) exit;

/**
 * Подключение IMask.js и маски телефона
 */
function rb_enqueue_imask() {
    if (is_admin()) {
        return;
    }

    wp_enqueue_script(
        'imask-js',
        plugin_dir_url(__FILE__) . 'imask.min.js',
        array(),
        '7.1.3',
        true
    );

    wp_add_inline_script('imask-js', "
document.addEventListener('DOMContentLoaded', function() {
    var phoneInputs = document.querySelectorAll('.phone-input');
    phoneInputs.forEach(function(input) {
        IMask(input, {
            mask: '+{7} (000) 000-00-00'
        });
    });
});
    ");
}
add_action('wp_enqueue_scripts', 'rb_enqueue_imask');
