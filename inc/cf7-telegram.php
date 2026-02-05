<?php
/**
 * RB Snippets - Contact Form 7 → Telegram
 *
 * Универсальный обработчик отправки заявок в Telegram
 */

if (!defined('ABSPATH')) exit;

/**
 * НАСТРОЙКИ
 */
$rb_tg_bot_token = '';  // Токен бота (получить у @BotFather)
$rb_tg_chat_id   = '';  // ID чата/группы (получить у @userinfobot или @getidsbot)

// ID форм, которые НЕ отправлять в Telegram (оставьте пустым для отправки всех)
$rb_tg_excluded_forms = array();

/**
 * Обработчик отправки CF7 в Telegram
 */
function rb_cf7_to_telegram($contact_form, &$abort, $submission) {
    global $rb_tg_bot_token, $rb_tg_chat_id, $rb_tg_excluded_forms;

    // Проверяем настройки
    if (empty($rb_tg_bot_token) || empty($rb_tg_chat_id)) {
        return $submission;
    }

    // Проверяем исключения
    $form_id = $contact_form->id();
    if (!empty($rb_tg_excluded_forms) && in_array($form_id, $rb_tg_excluded_forms)) {
        return $submission;
    }

    // Получаем данные
    $form_data = $submission->get_posted_data();
    $form_title = $contact_form->title();
    $site_name = get_bloginfo('name');
    $page_url = get_permalink();

    // Стандартные поля формы
    $field_mapping = array(
        'phone'   => array('your-phone', 'your_phone', 'phone', 'tel', 'telephone'),
        'email'   => array('your-email', 'your_email', 'email'),
        'name'    => array('your-name', 'your_name', 'name', 'firstname'),
        'message' => array('your-message', 'your_message', 'message', 'comments'),
    );

    $phone   = rb_tg_find_field($form_data, $field_mapping['phone']);
    $email   = rb_tg_find_field($form_data, $field_mapping['email']);
    $name    = rb_tg_find_field($form_data, $field_mapping['name']);
    $message = rb_tg_find_field($form_data, $field_mapping['message']);

    // Формируем сообщение
    $text = "📩 *Новая заявка с сайта*\n";
    $text .= "━━━━━━━━━━━━━━━\n";
    $text .= "🌐 *Сайт:* {$site_name}\n";
    $text .= "📋 *Форма:* {$form_title}\n";
    $text .= "━━━━━━━━━━━━━━━\n";

    if ($name) {
        $text .= "👤 *Имя:* {$name}\n";
    }
    if ($phone) {
        $text .= "📱 *Телефон:* `{$phone}`\n";
    }
    if ($email) {
        $text .= "✉️ *Email:* {$email}\n";
    }
    if ($message) {
        $text .= "💬 *Сообщение:*\n{$message}\n";
    }

    // Дополнительные поля
    $skip_fields = array_merge(
        $field_mapping['phone'],
        $field_mapping['email'],
        $field_mapping['name'],
        $field_mapping['message'],
        array('_wpcf7', '_wpcf7_version', '_wpcf7_locale', '_wpcf7_unit_tag', '_wpcf7_container_post', 'sbjs_first', 'sbjs_current', 'sbjs_udata', 'ymcid')
    );

    $additional = '';
    foreach ($form_data as $field_name => $field_value) {
        if (!in_array($field_name, $skip_fields) && !empty($field_value)) {
            if (is_array($field_value)) {
                $field_value = implode(', ', $field_value);
            }
            // Делаем имя поля читаемым
            $label = ucfirst(str_replace(array('-', '_'), ' ', $field_name));
            $additional .= "• *{$label}:* {$field_value}\n";
        }
    }

    if (!empty($additional)) {
        $text .= "━━━━━━━━━━━━━━━\n";
        $text .= "📎 *Дополнительно:*\n{$additional}";
    }

    $text .= "━━━━━━━━━━━━━━━\n";
    $text .= "🔗 *Страница:* {$page_url}\n";

    // UTM-метки (если подключен utm-tracker.php)
    if (function_exists('rb_get_utm_params')) {
        $utm_data = rb_get_utm_params();
        if (!empty($utm_data)) {
            $utm_text = '';
            foreach ($utm_data as $key => $value) {
                $utm_text .= str_replace('UTM_', '', $key) . ": {$value}\n";
            }
            if (!empty($utm_text)) {
                $text .= "📊 *UTM:*\n{$utm_text}";
            }
        }
    }

    // Sourcebuster
    if (!empty($form_data['sbjs_current'])) {
        $sbjs = urldecode($form_data['sbjs_current']);
        if (preg_match('/src=([^|]+)/', $sbjs, $matches)) {
            $text .= "📈 *Источник:* {$matches[1]}\n";
        }
    }

    // Отправка в Telegram
    $api_url = "https://api.telegram.org/bot{$rb_tg_bot_token}/sendMessage";

    $response = wp_remote_post($api_url, array(
        'timeout' => 15,
        'body'    => array(
            'chat_id'    => $rb_tg_chat_id,
            'text'       => $text,
            'parse_mode' => 'Markdown',
        ),
    ));

    if (is_wp_error($response)) {
        error_log('RB Snippets TG: ' . $response->get_error_message());
    }

    return $submission;
}
add_action('wpcf7_before_send_mail', 'rb_cf7_to_telegram', 10, 3);

/**
 * Поиск значения поля среди возможных имён
 */
function rb_tg_find_field($form_data, $possible_fields) {
    foreach ($possible_fields as $field) {
        if (isset($form_data[$field]) && !empty($form_data[$field])) {
            return is_array($form_data[$field]) ? implode(', ', $form_data[$field]) : $form_data[$field];
        }
    }
    return null;
}
