<?php
/**
 * RB Snippets - SMTP Configuration
 *
 * Настройка SMTP для отправки почты WordPress
 * Настройки берутся из ACF options (Настройки сайта → SMTP)
 */

if (!defined('ABSPATH')) exit;

/**
 * Настройка SMTP из ACF options
 */
function rb_custom_wp_mail_smtp($phpmailer) {
    if (!function_exists('get_field')) {
        return;
    }

    $host     = get_field('smtp_host', 'option');
    $port     = get_field('smtp_port', 'option');
    $username = get_field('smtp_username', 'option');
    $password = get_field('smtp_password', 'option');
    $secure   = get_field('smtp_secure', 'option');
    $from     = get_field('smtp_from', 'option');
    $from_name = get_field('smtp_from_name', 'option');

    // Если SMTP сервер не указан - не настраиваем
    if (empty($host)) {
        return;
    }

    $phpmailer->isSMTP();
    $phpmailer->Host       = $host;
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = $port ?: 465;
    $phpmailer->SMTPSecure = $secure ?: 'ssl';
    $phpmailer->Username   = $username;
    $phpmailer->Password   = $password;

    if (!empty($from)) {
        $phpmailer->From = $from;
    }

    if (!empty($from_name)) {
        $phpmailer->FromName = $from_name;
    }
}
add_action('phpmailer_init', 'rb_custom_wp_mail_smtp');

/**
 * Фильтр для WPShop Contact Form (если используется)
 */
add_filter('wpshop_contact_form_email_from', function() {
    if (function_exists('get_field')) {
        $from = get_field('smtp_from', 'option');
        if (!empty($from)) {
            return $from;
        }
    }
    return get_option('admin_email');
});
