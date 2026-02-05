<?php
/**
 * RB Snippets - SEO Extensions
 *
 * SEO улучшения: подмена H1 из ACF полей и др.
 * Работает для любых типов записей и таксономий
 */

if (!defined('ABSPATH')) exit;

/**
 * Очистка заголовка архива от префиксов (Рубрика:, Метка: и т.д.)
 */
add_filter('get_the_archive_title', function ($title) {
    if (is_category()) {
        $title = single_cat_title('', false);
    } elseif (is_tag()) {
        $title = single_tag_title('', false);
    } elseif (is_tax()) {
        $title = single_term_title('', false);
    }
    return $title;
});

/**
 * Подмена заголовка на одиночной странице записи (Single)
 * Если есть ACF поле h1 - выводим его вместо стандартного заголовка
 */
add_filter('the_title', 'rb_acf_h1_singular', 10, 2);
function rb_acf_h1_singular($title, $id = null) {
    if (!is_admin()
        && is_singular()
        && in_the_loop()
        && is_main_query()
        && $id === get_queried_object_id()
    ) {
        if (function_exists('get_field')) {
            $acf_h1 = get_field('h1', $id);
            if (!empty($acf_h1)) {
                return esc_html($acf_h1);
            }
        }
    }
    return $title;
}

/**
 * Подмена названия таксономии на странице её архива
 * Если есть ACF поле h1 у термина - выводим его
 */
add_filter('single_term_title', 'rb_acf_h1_taxonomy_archive');
function rb_acf_h1_taxonomy_archive($term_name) {
    if (!is_admin() && (is_tax() || is_category() || is_tag())) {
        $term = get_queried_object();

        if ($term && function_exists('get_field')) {
            $acf_h1 = get_field('h1', $term);
            if (!empty($acf_h1)) {
                return esc_html($acf_h1);
            }
        }
    }
    return $term_name;
}
