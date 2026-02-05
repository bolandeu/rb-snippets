<?php
/**
 * RB Snippets - Sourcebuster.js
 *
 * Подключение библиотеки Sourcebuster для отслеживания источников трафика
 * и подмены телефонов в зависимости от источника
 * Настройки берутся из ACF options (Настройки сайта → Интеграции)
 */

if (!defined('ABSPATH')) exit;

/**
 * Парсинг маппинга телефонов из ACF options
 * Формат: source:phone (каждый с новой строки)
 *
 * @return array ['default' => '+7...', 'yandex' => '+7...', ...]
 */
function rb_parse_phone_mapping() {
    if (!function_exists('get_field')) {
        return array();
    }

    $mapping_text = get_field('phone_mapping', 'option');
    if (empty($mapping_text)) {
        return array();
    }

    $phones = array();
    $lines = preg_split('/\r\n|\r|\n/', trim($mapping_text));

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }

        // Разделитель - первое двоеточие
        $pos = strpos($line, ':');
        if ($pos === false) {
            continue;
        }

        $source = strtolower(trim(substr($line, 0, $pos)));
        $phone = trim(substr($line, $pos + 1));

        if (!empty($source) && !empty($phone)) {
            $phones[$source] = $phone;
        }
    }

    return $phones;
}

/**
 * Подключение Sourcebuster.js на публичных страницах
 */
function rb_enqueue_sourcebuster() {
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

    // Получаем маппинг телефонов
    $phones = rb_parse_phone_mapping();

    // Добавляем подмену телефонов если:
    // - есть default
    // - есть хотя бы один другой источник (иначе нечего подменять)
    // - default не является плейсхолдером
    $placeholder_phone = '+7 (495) 000-00-00';
    $has_real_default = !empty($phones['default']) && $phones['default'] !== $placeholder_phone;
    $has_other_sources = count($phones) > 1;

    if ($has_real_default && $has_other_sources) {
        // Формируем JS объект с телефонами
        $phones_js = array();
        foreach ($phones as $source => $phone) {
            $phones_js[] = '"' . esc_js($source) . '": "' . esc_js($phone) . '"';
        }
        $phones_json = '{' . implode(', ', $phones_js) . '}';

        $inline_js .= '
(function() {
    var phones = ' . $phones_json . ';
    var source = sbjs.get.current.src;
    var oldPhone = phones["default"];
    var newPhone = phones[source] || phones["default"];

    if (!oldPhone) return;

    document.querySelectorAll(".phone").forEach(function(element) {
        // Замена текста (только если нет дочерних элементов)
        if (!element.children.length) {
            element.textContent = element.textContent.replace(oldPhone, newPhone);
        }
        // Замена href
        if (element.getAttribute("href")) {
            element.setAttribute("href", "tel:" + newPhone.replace(/[^+0-9]/g, ""));
        }
    });
})();';
    }

    wp_add_inline_script('sourcebuster-js', $inline_js);
}
add_action('wp_enqueue_scripts', 'rb_enqueue_sourcebuster', 5);
