<?php

// [current_year] - вывод текущего года
function current_year_shortcode() {
    return date('Y');
}
add_shortcode('current_year', 'current_year_shortcode');

/**
 * Plugin Name: Custom shortcodes (MU)
 * Description: Adds special shortcodes [domain] and [url] shortcodes.
 * 
 * [domain link="true"] → кликабельный домен, ведущий на главную.
 * [url path="/privacy-policy/" link="true" text="Политика"] → ссылка “Политика”
*/

add_action('init', function () {

  // [domain] или [domain link="true"]
  add_shortcode('domain', function ($atts = []) {
    $atts = shortcode_atts([
      'link' => 'false',
      'text' => '',          // если нужно заменить текст ссылки
      'path' => '',          // если хотите чтобы домен ссылался не на главную, а на путь
    ], $atts, 'domain');

    $host = wp_parse_url( home_url(), PHP_URL_HOST );
    if ( ! $host ) return '';

    $link = in_array( strtolower((string)$atts['link']), ['1','true','yes'], true ); 
    if ( ! $link ) return esc_html( $host );

    $href = home_url( (string) $atts['path'] );
    $label = $atts['text'] !== '' ? (string) $atts['text'] : $host;

    return '<a href="' . esc_url( $href ) . '">' . esc_html( $label ) . '</a>'; 
  });

  // [url] или [url link="true"] или [url path="/privacy-policy/" link="true"]
  add_shortcode('url', function ($atts = []) {
    $atts = shortcode_atts([
      'path' => '',
      'link' => 'false',
      'text' => '',          // текст ссылки (по умолчанию сам URL)
    ], $atts, 'url'); 

    $path = (string) $atts['path'];
    if ( $path && $path[0] !== '/' ) $path = '/' . $path;

    $href = home_url( $path );
    $link = in_array( strtolower((string)$atts['link']), ['1','true','yes'], true ); 

    if ( ! $link ) return esc_html( $href );

    $label = $atts['text'] !== '' ? (string) $atts['text'] : $href;
    return '<a href="' . esc_url( $href ) . '">' . esc_html( $label ) . '</a>'; 
  });

});


/**
 * Шорткод для вывода контента или конкретных блоков другой страницы.
 * * ПРИМЕРЫ ИСПОЛЬЗОВАНИЯ:
 * [page_content id="123"]                 - Вывести всё содержимое страницы с ID 123
 * [page_content slug="about-us"]          - Вывести всё содержимое страницы по её ярлыку
 * [page_content slug="services" block="1"] - Вывести только ПЕРВЫЙ блок верхнего уровня страницы "services"
 * [page_content id="42" block="2-4"]      - Вывести блоки со второго по четвёртый включительно
 */

function wp_get_page_content_advanced_shortcode($atts) {
    // 1. Настройка атрибутов
    $atts = shortcode_atts(array(
        'id'    => null,
        'slug'  => null,
        'block' => null, // Номер блока (напр. "1") или диапазон (напр. "1-3")
    ), $atts);

    $target_page = null;

    // 2. Поиск страницы (по ID или по ярлыку)
    if (!empty($atts['id'])) {
        $target_page = get_post($atts['id']);
    } elseif (!empty($atts['slug'])) {
        // Ищем среди страниц и постов
        $target_page = get_page_by_path($atts['slug'], OBJECT, array('page', 'post'));
    }

    // Проверка: найдена ли страница и не пытаемся ли мы вызвать её саму в себе
    if (!$target_page || $target_page->ID === get_the_ID()) {
        return '';
    }

    // 3. Обработка вывода
    $output = '';

    // Если указан конкретный блок или диапазон
    if (!empty($atts['block'])) {
        // Разбираем контент на блоки Gutenberg
        $all_blocks = parse_blocks($target_page->post_content);

        // Очищаем массив от пустых блоков (пробелы, переносы строк между блоками)
        // Оставляем только те, у которых есть имя или реальное содержимое
        $clean_blocks = array_values(array_filter($all_blocks, function($block) {
            $has_name = !empty($block['blockName']);
            $has_content = trim(strip_tags($block['innerHTML'], '<img><iframe><br>')) !== '';
            return $has_name || $has_content;
        }));

        $selected_blocks = array();

        // Проверяем, указан ли диапазон (напр. 1-3)
        if (strpos($atts['block'], '-') !== false) {
            list($start, $end) = explode('-', $atts['block']);
            $start_idx = max(0, intval($start) - 1);
            $count = intval($end) - $start_idx;
            $selected_blocks = array_slice($clean_blocks, $start_idx, $count);
        } else {
            // Если указан один конкретный номер блока
            $idx = intval($atts['block']) - 1;
            if (isset($clean_blocks[$idx])) {
                $selected_blocks[] = $clean_blocks[$idx];
            }
        }

        // Рендерим выбранные блоки
        foreach ($selected_blocks as $block) {
            $output .= render_block($block);
        }
    } else {
        // Если параметр block не задан — выводим весь контент страницы
        $output = apply_filters('the_content', $target_page->post_content);
    }

    return $output;
}

// Регистрация шорткода
add_shortcode('page_content', 'wp_get_page_content_advanced_shortcode');