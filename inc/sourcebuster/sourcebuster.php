<?php
/**
 * RB Snippets - Sourcebuster.js
 *
 * Подключение библиотеки Sourcebuster для отслеживания источников трафика
 * и подмены телефонов в зависимости от источника
 */

if (!defined('ABSPATH')) exit;

/**
 * Настройки подмены телефонов
 * Оставьте пустыми, чтобы отключить подмену
 */
$rb_phone_default = '+7 (495) 275-30-85';  // Телефон по умолчанию
$rb_phone_yandex  = '+7 (495) 275-30-88';  // Телефон для Яндекса

/**
 * Подключение Sourcebuster.js на публичных страницах
 */
function rb_enqueue_sourcebuster() {
    global $rb_phone_default, $rb_phone_yandex;

    // Только на публичных страницах
    if (is_admin()) {
        return;
    }

    // Подключение sourcebuster.min.js из папки плагина
    wp_enqueue_script(
        'sourcebuster-js',
        plugin_dir_url(__FILE__) . 'sourcebuster.min.js',
        array(),
        '1.0.0',
        false // В head, чтобы куки были доступны до загрузки страницы
    );

    // Инициализация Sourcebuster
    $domain = isset($_SERVER['SERVER_NAME']) ? esc_js($_SERVER['SERVER_NAME']) : '';

    // Подготовка JS кода
    $inline_js = 'sbjs.init({
        domain: "' . $domain . '",
        isolate: true
    });';

    // Добавляем подмену телефонов если настроены
    if (!empty($rb_phone_default)) {
        $old_phone = esc_js($rb_phone_default);
        $def_phone = esc_js($rb_phone_default);
        $yandex_phone = !empty($rb_phone_yandex) ? esc_js($rb_phone_yandex) : $def_phone;

        $inline_js .= '
(function() {
    var source = sbjs.get.current.src;
    var oldPhone = "' . $old_phone . '";
    var defPhone = "' . $def_phone . '";
    var yandexPhone = "' . $yandex_phone . '";

    if (source === "yandex") {
        defPhone = yandexPhone;
    }

    document.querySelectorAll(".phone").forEach(function(element) {
        // Замена текста (только если нет дочерних элементов)
        if (!element.children.length) {
            element.textContent = element.textContent.replace(oldPhone, defPhone);
        }
        // Замена href
        if (element.getAttribute("href")) {
            element.setAttribute("href", "tel:" + defPhone.replace(/[^+0-9]/g, ""));
        }
    });
})();';
    }

    wp_add_inline_script('sourcebuster-js', $inline_js);
}
add_action('wp_enqueue_scripts', 'rb_enqueue_sourcebuster', 5);
