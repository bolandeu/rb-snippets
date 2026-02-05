<?php
/**
 * RB Snippets - Contact Form 7 → Битрикс24
 *
 * Универсальный обработчик отправки заявок в Битрикс24 CRM
 */

if (!defined('ABSPATH')) exit;

/**
 * Получить webhook Битрикс24 из ACF options
 */
function rb_get_b24_webhook() {
    if (function_exists('get_field')) {
        return get_field('b24_webhook', 'option') ?: '';
    }
    return '';
}

/**
 * Парсинг маппинга пользовательских полей Битрикс24 из ACF options
 * Формат: field_name:UF_CRM_xxx (каждое с новой строки)
 *
 * @return array ['page_url' => 'UF_CRM_xxx', 'site_domain' => 'UF_CRM_xxx', ...]
 */
function rb_parse_b24_custom_fields() {
    if (!function_exists('get_field')) {
        return array();
    }

    $mapping_text = get_field('b24_custom_fields', 'option');
    if (empty($mapping_text)) {
        return array();
    }

    $fields = array();
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

        $field_name = strtolower(trim(substr($line, 0, $pos)));
        $uf_code = trim(substr($line, $pos + 1));

        if (!empty($field_name) && !empty($uf_code)) {
            $fields[$field_name] = $uf_code;
        }
    }

    return $fields;
}

/**
 * Обработчик отправки CF7 в Битрикс24
 */
function rb_cf7_to_bitrix24($contact_form, &$abort, $submission) {
    $rb_b24_webhook = rb_get_b24_webhook();
    $rb_b24_custom_fields = rb_parse_b24_custom_fields();

    // Проверяем настройки
    if (empty($rb_b24_webhook)) {
        return $submission;
    }

    // Проверяем исключения
    $form_id = $contact_form->id();
    if (!empty($rb_b24_excluded_forms) && in_array($form_id, $rb_b24_excluded_forms)) {
        return $submission;
    }

    // Получаем данные
    $form_data = $submission->get_posted_data();
    $site_name = get_bloginfo('name');
    $site_domain = wp_parse_url(home_url(), PHP_URL_HOST);

    // Базовые поля лида
    $lead_data = array(
        'fields' => array(
            'TITLE'  => 'Заявка с сайта - ' . $site_name,
            'OPENED' => 'Y',
        )
    );

    // Добавляем пользовательские поля если заданы
    if (!empty($rb_b24_custom_fields['site_domain'])) {
        $lead_data['fields'][$rb_b24_custom_fields['site_domain']] = $site_domain;
    }
    if (!empty($rb_b24_custom_fields['referer'])) {
        $lead_data['fields'][$rb_b24_custom_fields['referer']] = $_SERVER['HTTP_REFERER'] ?? get_permalink();
    }
    if (!empty($rb_b24_custom_fields['page_url'])) {
        $lead_data['fields'][$rb_b24_custom_fields['page_url']] = get_permalink();
    }

    // UTM-метки (если подключен utm-tracker.php)
    if (function_exists('rb_get_utm_params')) {
        $utm_data = rb_get_utm_params();
        if (!empty($utm_data)) {
            $lead_data['fields'] = array_merge($lead_data['fields'], $utm_data);
        }
    }

    // Стандартные поля формы
    $field_mapping = array(
        'phone'   => array('your-phone', 'your_phone', 'phone', 'tel', 'telephone'),
        'email'   => array('your-email', 'your_email', 'email'),
        'name'    => array('your-name', 'your_name', 'name', 'firstname'),
        'message' => array('your-message', 'your_message', 'message', 'comments'),
    );

    // Телефон
    $phone = rb_b24_find_field($form_data, $field_mapping['phone']);
    if ($phone) {
        $lead_data['fields']['PHONE'] = array(array(
            'VALUE'      => $phone,
            'VALUE_TYPE' => 'WORK'
        ));
    }

    // Email
    $email = rb_b24_find_field($form_data, $field_mapping['email']);
    if ($email) {
        $lead_data['fields']['EMAIL'] = array(array(
            'VALUE'      => $email,
            'VALUE_TYPE' => 'WORK'
        ));
    }

    // Имя
    $name = rb_b24_find_field($form_data, $field_mapping['name']);
    if ($name) {
        $lead_data['fields']['NAME'] = $name;
    }

    // Сообщение
    $message = rb_b24_find_field($form_data, $field_mapping['message']);
    $comments = $message ? $message . "\n\n" : '';

    // Дополнительные поля в комментарий
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
            $additional .= "{$field_name}: {$field_value}\n";
        }
    }

    if (!empty($additional)) {
        $comments .= "Дополнительно:\n" . $additional;
    }

    $lead_data['fields']['COMMENTS'] = $comments;

    // Yandex Metrika Client ID
    if (!empty($form_data['ymcid']) && !empty($rb_b24_custom_fields['ymcid'])) {
        $lead_data['fields'][$rb_b24_custom_fields['ymcid']] = $form_data['ymcid'];
    }

    // Sourcebuster данные
    if (!empty($form_data['sbjs_current'])) {
        if (!empty($form_data['sbjs_first']) && !empty($rb_b24_custom_fields['sbjs_first'])) {
            $lead_data['fields'][$rb_b24_custom_fields['sbjs_first']] = urldecode($form_data['sbjs_first']);
        }
        if (!empty($rb_b24_custom_fields['sbjs_current'])) {
            $lead_data['fields'][$rb_b24_custom_fields['sbjs_current']] = urldecode($form_data['sbjs_current']);
        }
        if (!empty($form_data['sbjs_udata']) && !empty($rb_b24_custom_fields['sbjs_udata'])) {
            $lead_data['fields'][$rb_b24_custom_fields['sbjs_udata']] = urldecode($form_data['sbjs_udata']);
        }

        // Определяем SOURCE_ID по sourcebuster
        $sbjs_current = urldecode($form_data['sbjs_current']);
        $source_id = 'OTHER';
        $source_desc = '';

        // Парсим источник
        if (preg_match('/src=([^|]+)/', $sbjs_current, $matches)) {
            $source_desc = $matches[1];
        }

        if (strpos($sbjs_current, 'organic') !== false) {
            $source_id = 1; // SEO
        } elseif (strpos($sbjs_current, 'referral') !== false) {
            $source_id = 'WEB'; // Реферальный
        } elseif (strpos($sbjs_current, 'cpc') !== false) {
            $source_id = 3; // PPC/Реклама
        }

        $lead_data['fields']['SOURCE_ID'] = $source_id;
        if (!empty($source_desc)) {
            $lead_data['fields']['SOURCE_DESCRIPTION'] = $source_desc;
        }
    }

    // Отправка в Битрикс24
    $response = wp_remote_post($rb_b24_webhook . 'crm.lead.add.json', array(
        'timeout' => 15,
        'body'    => $lead_data,
    ));

    if (is_wp_error($response)) {
        error_log('RB Snippets B24: ' . $response->get_error_message());
    }

    return $submission;
}
add_action('wpcf7_before_send_mail', 'rb_cf7_to_bitrix24', 10, 3);

/**
 * Поиск значения поля среди возможных имён
 */
function rb_b24_find_field($form_data, $possible_fields) {
    foreach ($possible_fields as $field) {
        if (isset($form_data[$field]) && !empty($form_data[$field])) {
            return is_array($form_data[$field]) ? implode(', ', $form_data[$field]) : $form_data[$field];
        }
    }
    return null;
}
