<?php
/**
 * Plugin Name: RB Snippets
 * Description: Набор полезных снипетов для быстрого запуска сайта.
 * Version:     1.0.2
 * Author:      Roman Bolandeu
 * GitHub Plugin URI: https://github.com/bolandeu/rb-snippets
 */

if (!defined('ABSPATH')) exit;


// ============================================================================
// 1. ОБНОВЛЕНИЯ ПЛАГИНА ЧЕРЕЗ GITHUB
// ============================================================================
if (file_exists(__DIR__ . '/vendors/plugin-update-checker/plugin-update-checker.php')) {
    require_once __DIR__ . '/vendors/plugin-update-checker/plugin-update-checker.php';

    $myUpdateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/bolandeu/rb-snippets/',
        __FILE__,
        'rb-snippets'
    );
    $myUpdateChecker->setBranch('main');
}


// ============================================================================
// 2. БАЗОВЫЕ СНИППЕТЫ (всегда активны)
// ============================================================================

// Шорткоды: [domain], [url], [page_content]
require_once __DIR__ . '/inc/shortcodes.php';

// ============================================================================
// 3. АНАЛИТИКА И ОТСЛЕЖИВАНИЕ
// ============================================================================

// UTM Tracker: сохранение UTM-меток в сессию и cookies
// Функция: rb_get_utm_params() для получения меток
require_once __DIR__ . '/inc/utm-tracker.php';

// Яндекс.Метрика: счетчик + отслеживание событий (клики, копирование, формы)
// Настройка: $ywm_counter в файле
require_once __DIR__ . '/inc/tag-manager.php';

// Sourcebuster.js: отслеживание источников трафика + подмена телефонов
// Настройка: $rb_phone_default, $rb_phone_yandex в файле
require_once __DIR__ . '/inc/sourcebuster/sourcebuster.php';


// ============================================================================
// 4. ИНТЕГРАЦИИ С CRM И МЕССЕНДЖЕРАМИ
// ============================================================================

// Contact Form 7 → Битрикс24: отправка заявок в CRM
// Настройка: $rb_b24_webhook, $rb_b24_custom_fields в файле
require_once __DIR__ . '/inc/cf7-bitrix24.php';

// Contact Form 7 → Telegram: уведомления о заявках в бота
// Настройка: $rb_tg_bot_token, $rb_tg_chat_id в файле
require_once __DIR__ . '/inc/cf7-telegram.php';


// ============================================================================
// 5. ACF РАСШИРЕНИЯ (раскомментируйте при необходимости)
// ============================================================================

// Шорткод [sf] для вывода полей ACF
// require_once __DIR__ . '/inc/acf-shortcode.php';

// Страница "Настройки сайта" для ACF options
// require_once __DIR__ . '/inc/acf-site-settings.php';

// REST API для ACF Options (GET/POST) и любых типов записей и таксономий
// require_once __DIR__ . '/inc/rest-api-extensions.php';


// ============================================================================
// 6. ПРОЧИЕ СНИППЕТЫ (раскомментируйте при необходимости)
// ============================================================================

// SMTP настройки для отправки почты
// require_once __DIR__ . '/inc/smtp.php';

// Permalink Manager Pro: REST API интеграция
// require_once __DIR__ . '/inc/permalink-manager-rest.php';


// ============================================================================
// 7. СТРАНИЦА ПОМОЩИ В АДМИНКЕ
// ============================================================================
require_once __DIR__ . '/inc/admin-help-page.php';
