<?php
/**
 * Plugin Name: Permalink Manager REST API
 * Description: Добавляет поле custom_permalink в WordPress REST API для интеграции с Permalink Manager Pro
 * Version: 1.0.0
 * Author: Custom Development
 * License: GPL v2 or later
 */

// Запрет прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Проверка наличия Permalink Manager Pro
 *
 * @return bool
 */
function permalink_manager_rest_is_active() {
    return class_exists('Permalink_Manager_URI_Functions')
        && class_exists('Permalink_Manager_Helper_Functions');
}

/**
 * Регистрация REST API полей для Permalink Manager
 */
add_action('rest_api_init', 'register_permalink_manager_rest_fields');

function register_permalink_manager_rest_fields() {
    // Проверка активности Permalink Manager Pro
    if (!permalink_manager_rest_is_active()) {
        return;
    }

    // Регистрация для всех публичных типов постов с REST API поддержкой
    $post_types = get_post_types([
        'public' => true,
        'show_in_rest' => true,
    ], 'names');

    foreach ($post_types as $post_type) {
        register_rest_field($post_type, 'custom_permalink', [
            'get_callback' => 'pm_rest_get_post_custom_permalink',
            'update_callback' => 'pm_rest_update_post_custom_permalink',
            'schema' => [
                'type' => 'string',
                'description' => 'Custom permalink URI (управляется Permalink Manager Pro)',
                'context' => ['view', 'edit'],
            ],
        ]);
    }

    // Регистрация для всех таксономий с REST API поддержкой
    $taxonomies = get_taxonomies(['show_in_rest' => true], 'names');

    foreach ($taxonomies as $taxonomy) {
        register_rest_field($taxonomy, 'custom_permalink', [
            'get_callback' => 'pm_rest_get_term_custom_permalink',
            'update_callback' => 'pm_rest_update_term_custom_permalink',
            'schema' => [
                'type' => 'string',
                'description' => 'Custom permalink URI для термина таксономии',
                'context' => ['view', 'edit'],
            ],
        ]);
    }
}

/**
 * Получение custom permalink для поста
 *
 * @param array $object Массив данных поста
 * @param string $field_name Имя поля
 * @param WP_REST_Request $request REST запрос
 * @return string|null Custom permalink или null
 */
function pm_rest_get_post_custom_permalink($object, $field_name, $request) {
    global $permalink_manager_uris;

    // Проверка инициализации глобальной переменной
    if (!is_array($permalink_manager_uris)) {
        return null;
    }

    $post_id = $object['id'];

    // Возвращаем custom URI если есть, иначе null
    return isset($permalink_manager_uris[$post_id])
        ? $permalink_manager_uris[$post_id]
        : null;
}

/**
 * Обновление custom permalink для поста
 *
 * @param mixed $value Новое значение
 * @param WP_Post $object Объект поста
 * @param string $field_name Имя поля
 * @return bool|WP_Error true при успехе или WP_Error при ошибке
 */
function pm_rest_update_post_custom_permalink($value, $object, $field_name) {
    // Если значение пустое, удаляем custom permalink
    if (empty($value) || $value === '') {
        Permalink_Manager_URI_Functions::remove_single_uri(
            $object->ID,
            false,  // не таксономия
            true    // сохранить в БД
        );

        return true;
    }

    // Валидация URI
    $validation_result = pm_rest_validate_uri($value, $object->ID, false);

    if (is_wp_error($validation_result)) {
        return $validation_result;
    }

    // Санитизация через Permalink Manager
    $clean_uri = Permalink_Manager_Helper_Functions::sanitize_title($value, true);

    // Проверка уникальности
    $uniqueness_check = pm_rest_check_uri_uniqueness($clean_uri, $object->ID, false);

    if (is_wp_error($uniqueness_check)) {
        return $uniqueness_check;
    }

    // Получаем старый URI и native/default URIs для хука
    global $permalink_manager_uris;

    // Получаем native и default URIs через функции Permalink Manager
    $native_uri = Permalink_Manager_URI_Functions_Post::get_default_post_uri($object->ID, true);
    $default_uri = Permalink_Manager_URI_Functions_Post::get_default_post_uri($object->ID, false);

    $old_uri = isset($permalink_manager_uris[$object->ID])
        ? $permalink_manager_uris[$object->ID]
        : $native_uri;

    // Сохранение
    Permalink_Manager_URI_Functions::save_single_uri(
        $object->ID,
        $clean_uri,
        false,  // не таксономия
        true    // сохранить в БД
    );

    // Хук после обновления (для совместимости с Permalink Manager Pro)
    // Передаем все 5 параметров как требует Permalink_Manager_Pro_Functions::save_redirects()
    do_action('permalink_manager_updated_post_uri', $object->ID, $clean_uri, $old_uri, $native_uri, $default_uri, true, true);

    return true;
}

/**
 * Получение custom permalink для термина таксономии
 *
 * @param array $object Массив данных термина
 * @param string $field_name Имя поля
 * @param WP_REST_Request $request REST запрос
 * @return string|null Custom permalink или null
 */
function pm_rest_get_term_custom_permalink($object, $field_name, $request) {
    global $permalink_manager_uris;

    // Проверка инициализации глобальной переменной
    if (!is_array($permalink_manager_uris)) {
        return null;
    }

    $term_id = $object['id'];
    $key = "tax-{$term_id}";

    return isset($permalink_manager_uris[$key])
        ? $permalink_manager_uris[$key]
        : null;
}

/**
 * Обновление custom permalink для термина таксономии
 *
 * @param mixed $value Новое значение
 * @param WP_Term|array $object Объект WP_Term или массив данных термина
 * @param string $field_name Имя поля
 * @return bool|WP_Error true при успехе или WP_Error при ошибке
 */
function pm_rest_update_term_custom_permalink($value, $object, $field_name) {
    // WordPress REST API может передавать как объект WP_Term, так и массив
    $term_id = is_array($object) ? $object['id'] : $object->term_id;

    // Если значение пустое, удаляем custom permalink
    if (empty($value) || $value === '') {
        Permalink_Manager_URI_Functions::remove_single_uri(
            $term_id,
            true,   // таксономия
            true    // сохранить в БД
        );

        return true;
    }

    // Валидация URI
    $validation_result = pm_rest_validate_uri($value, $term_id, true);

    if (is_wp_error($validation_result)) {
        return $validation_result;
    }

    // Санитизация через Permalink Manager
    $clean_uri = Permalink_Manager_Helper_Functions::sanitize_title($value, true);

    // Проверка уникальности
    $uniqueness_check = pm_rest_check_uri_uniqueness($clean_uri, $term_id, true);

    if (is_wp_error($uniqueness_check)) {
        return $uniqueness_check;
    }

    // Получаем старый URI и native/default URIs для хука
    global $permalink_manager_uris;
    $key = "tax-{$term_id}";

    // Получаем native и default URIs через функции Permalink Manager
    $native_uri = Permalink_Manager_URI_Functions_Tax::get_default_term_uri($term_id, true);
    $default_uri = Permalink_Manager_URI_Functions_Tax::get_default_term_uri($term_id, false);

    $old_uri = isset($permalink_manager_uris[$key])
        ? $permalink_manager_uris[$key]
        : $native_uri;

    // Сохранение
    Permalink_Manager_URI_Functions::save_single_uri(
        $term_id,
        $clean_uri,
        true,   // таксономия
        true    // сохранить в БД
    );

    // Хук после обновления (для совместимости с Permalink Manager Pro)
    // Передаем все 5 параметров как требует Permalink_Manager_Pro_Functions::save_redirects()
    do_action('permalink_manager_updated_term_uri', $term_id, $clean_uri, $old_uri, $native_uri, $default_uri, true, true);

    return true;
}

/**
 * Валидация формата URI
 *
 * @param string $uri URI для проверки
 * @param int $element_id ID элемента (поста или термина)
 * @param bool $is_tax true если таксономия
 * @return true|WP_Error true если валидно или WP_Error при ошибке
 */
function pm_rest_validate_uri($uri, $element_id, $is_tax) {
    // Проверка типа
    if (!is_string($uri)) {
        return new WP_Error(
            'invalid_uri_format',
            'URI должен быть строкой',
            ['status' => 400]
        );
    }

    $uri_trimmed = trim($uri, " /-");

    // Проверка на пробелы
    if (preg_match('/\s/', $uri_trimmed)) {
        return new WP_Error(
            'invalid_uri_format',
            'URI не должен содержать пробелы. Используйте дефисы (-) вместо пробелов.',
            ['status' => 400]
        );
    }

    // Проверка на опасные символы
    $dangerous_chars = ['<', '>', '"', "'", '&', '?', '#'];
    foreach ($dangerous_chars as $char) {
        if (strpos($uri_trimmed, $char) !== false) {
            return new WP_Error(
                'invalid_uri_format',
                sprintf('URI содержит недопустимый символ: %s', $char),
                ['status' => 400]
            );
        }
    }

    return true;
}

/**
 * Проверка уникальности URI
 *
 * @param string $uri URI для проверки
 * @param int $element_id ID элемента (поста или термина)
 * @param bool $is_tax true если таксономия
 * @return true|WP_Error true если уникален или WP_Error при конфликте
 */
function pm_rest_check_uri_uniqueness($uri, $element_id, $is_tax) {
    global $permalink_manager_uris;

    // Проверка инициализации глобальной переменной
    if (!is_array($permalink_manager_uris)) {
        return true; // Если массив не инициализирован, считаем URI уникальным
    }

    $uri_clean = trim($uri, " /-");

    // Получаем ключ текущего элемента
    $current_key = $is_tax ? "tax-{$element_id}" : $element_id;

    // Проверяем все URI
    foreach ($permalink_manager_uris as $key => $existing_uri) {
        // Пропускаем текущий элемент
        if ($key == $current_key) {
            continue;
        }

        // Сравниваем URI
        if (trim($existing_uri, " /-") === $uri_clean) {
            // Определяем тип конфликтующего элемента
            if (strpos($key, 'tax-') === 0) {
                $conflict_id = str_replace('tax-', '', $key);
                $term = get_term($conflict_id);
                $conflict_type = 'термин таксономии';
                $conflict_name = $term && !is_wp_error($term) ? $term->name : "ID {$conflict_id}";
            } else {
                $post = get_post($key);
                $conflict_type = 'пост';
                $conflict_name = $post ? $post->post_title : "ID {$key}";
            }

            return new WP_Error(
                'duplicate_uri',
                sprintf("URI '%s' уже используется (%s: %s)", $uri_clean, $conflict_type, $conflict_name),
                ['status' => 409]
            );
        }
    }

    return true;
}

/**
 * Хелпер-функция: Получить custom URI для любого элемента
 *
 * @param int $element_id ID поста или термина
 * @param bool $is_tax true для таксономии, false для поста
 * @return string|null Custom URI или null
 */
function pm_get_custom_uri($element_id, $is_tax = false) {
    if (!permalink_manager_rest_is_active()) {
        return null;
    }

    global $permalink_manager_uris;

    // Проверка инициализации глобальной переменной
    if (!is_array($permalink_manager_uris)) {
        return null;
    }

    $key = $is_tax ? "tax-{$element_id}" : $element_id;

    return isset($permalink_manager_uris[$key])
        ? $permalink_manager_uris[$key]
        : null;
}

/**
 * Хелпер-функция: Получить полный URL с custom permalink
 *
 * @param int $element_id ID поста или термина
 * @param bool $is_tax true для таксономии, false для поста
 * @return string|null Полный URL или null
 */
function pm_get_full_url($element_id, $is_tax = false) {
    $uri = pm_get_custom_uri($element_id, $is_tax);

    if (!$uri) {
        return null;
    }

    return home_url($uri);
}
