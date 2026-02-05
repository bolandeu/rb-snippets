<?php
/**
 * RB Snippets - UTM Tracker
 *
 * Сохранение UTM-меток в сессию и cookies
 */

if (!defined('ABSPATH')) exit;

/**
 * Сохранение UTM-меток при первом посещении
 */
function rb_save_utm_data() {
    $utm_params = array(
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term'
    );

    $has_utm = false;
    foreach ($utm_params as $param) {
        if (!empty($_GET[$param])) {
            $has_utm = true;
            break;
        }
    }

    // Если нет UTM в URL - выходим
    if (!$has_utm) {
        return;
    }

    // Стартуем сессию если нужно
    if (!session_id() && !headers_sent()) {
        session_start();
    }

    $utm_data = array();

    foreach ($utm_params as $param) {
        if (!empty($_GET[$param])) {
            $value = sanitize_text_field($_GET[$param]);
            $key = strtoupper(str_replace('utm_', 'UTM_', $param));

            $utm_data[$key] = $value;

            // Сохраняем в cookie на 30 дней
            if (!headers_sent()) {
                setcookie($param, $value, time() + (86400 * 30), '/');
            }
        }
    }

    if (!empty($utm_data) && isset($_SESSION)) {
        $_SESSION['utm_data'] = $utm_data;
    }
}
add_action('init', 'rb_save_utm_data');

/**
 * Получить UTM-метки
 *
 * @return array Массив UTM-меток в формате ['UTM_SOURCE' => 'value', ...]
 */
function rb_get_utm_params() {
    $utm_fields = array(
        'utm_source'   => 'UTM_SOURCE',
        'utm_medium'   => 'UTM_MEDIUM',
        'utm_campaign' => 'UTM_CAMPAIGN',
        'utm_content'  => 'UTM_CONTENT',
        'utm_term'     => 'UTM_TERM'
    );

    $utm_data = array();

    // 1. Пробуем из $_GET (приоритет)
    foreach ($utm_fields as $utm_param => $field_key) {
        if (!empty($_GET[$utm_param])) {
            $utm_data[$field_key] = sanitize_text_field($_GET[$utm_param]);
        }
    }

    if (!empty($utm_data)) {
        return $utm_data;
    }

    // 2. Пробуем из сессии
    if (isset($_SESSION['utm_data']) && is_array($_SESSION['utm_data'])) {
        return $_SESSION['utm_data'];
    }

    // 3. Пробуем из cookies
    foreach ($utm_fields as $utm_param => $field_key) {
        if (!empty($_COOKIE[$utm_param])) {
            $utm_data[$field_key] = sanitize_text_field($_COOKIE[$utm_param]);
        }
    }

    return $utm_data;
}
