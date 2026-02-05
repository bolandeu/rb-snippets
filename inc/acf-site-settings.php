<?php
/**
 * Plugin Name: ACF Site Settings
 * Description: Автоматически создает страницу настроек сайта в админ-панели через ACF Options Page
 * Version: 1.0.0
 * Author: Custom Development
 * License: GPL v2 or later
 */

// Запрет прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Регистрация страницы настроек ACF
 *
 * Создает страницу "Настройки сайта" в меню WordPress админки.
 * Эта страница будет доступна через REST API эндпоинт /wp-json/siteoptions/v1/options
 */
add_action('acf/init', 'register_acf_site_settings_page');

function register_acf_site_settings_page() {
    // Проверяем, что функция ACF доступна
    if (!function_exists('acf_add_options_page')) {
        return;
    }

    // Основная страница настроек
    acf_add_options_page(array(
        'page_title'    => 'Настройки сайта',          // Заголовок страницы
        'menu_title'    => 'Настройки сайта',          // Название в меню
        'menu_slug'     => 'site-settings',            // Slug для URL
        'capability'    => 'manage_options',           // Права доступа (только администраторы)
        'icon_url'      => 'dashicons-admin-settings', // Иконка в меню
        'position'      => 60,                         // Позиция в меню (после "Внешний вид")
        'redirect'      => false,                      // Не перенаправлять на первую подстраницу
        'post_id'       => 'options',                  // ID для сохранения полей (используется в REST API)
        'autoload'      => true,                       // Автозагрузка опций при каждом запросе
        'update_button' => 'Сохранить изменения',      // Текст кнопки сохранения
        'updated_message' => 'Настройки обновлены',    // Сообщение после сохранения
    ));

    // Подстраницы (раскомментируйте если нужны)
    /*
    // Подстраница: Контактная информация
    acf_add_options_sub_page(array(
        'page_title'  => 'Контактная информация',
        'menu_title'  => 'Контакты',
        'menu_slug'   => 'site-contacts',
        'parent_slug' => 'site-settings',
        'capability'  => 'manage_options',
    ));

    // Подстраница: Социальные сети
    acf_add_options_sub_page(array(
        'page_title'  => 'Социальные сети',
        'menu_title'  => 'Соцсети',
        'menu_slug'   => 'site-social',
        'parent_slug' => 'site-settings',
        'capability'  => 'manage_options',
    ));

    // Подстраница: SEO настройки
    acf_add_options_sub_page(array(
        'page_title'  => 'SEO настройки',
        'menu_title'  => 'SEO',
        'menu_slug'   => 'site-seo',
        'parent_slug' => 'site-settings',
        'capability'  => 'manage_options',
    ));

    // Подстраница: Внешний вид
    acf_add_options_sub_page(array(
        'page_title'  => 'Настройки внешнего вида',
        'menu_title'  => 'Внешний вид',
        'menu_slug'   => 'site-appearance',
        'parent_slug' => 'site-settings',
        'capability'  => 'manage_options',
    ));
    */
}

/**
 * Регистрация группы полей программно
 *
 * Автоматически создает базовые поля настроек сайта
 */
add_action('acf/init', 'register_default_site_settings_fields');

function register_default_site_settings_fields() {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group(array(
        'key' => 'group_site_settings',
        'title' => 'Основные настройки',
        'fields' => array(
            // ============================================================
            // ВКЛАДКА: Реквизиты компании
            // ============================================================
            array(
                'key' => 'field_tab_company',
                'label' => 'Реквизиты компании',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
                'endpoint' => 0,
            ),
            // Полное наименование
            array(
                'key' => 'field_fullname',
                'label' => 'Полное наименование',
                'name' => 'fullname',
                'type' => 'text',
                'required' => 0,
            ),
            // Краткое наименование
            array(
                'key' => 'field_name',
                'label' => 'Краткое наименование',
                'name' => 'name',
                'type' => 'text',
                'required' => 0,
            ),
            // Юридический адрес
            array(
                'key' => 'field_juraddress',
                'label' => 'Юридический адрес',
                'name' => 'juraddress',
                'type' => 'text',
                'required' => 0,
            ),
            // Фактический адрес
            array(
                'key' => 'field_address',
                'label' => 'Фактический адрес',
                'name' => 'address',
                'type' => 'text',
                'required' => 0,
            ),
            // ОГРН
            array(
                'key' => 'field_ogrn',
                'label' => 'ОГРН',
                'name' => 'ogrn',
                'type' => 'text',
                'required' => 0,
            ),
            // ИНН
            array(
                'key' => 'field_inn',
                'label' => 'ИНН',
                'name' => 'inn',
                'type' => 'text',
                'required' => 0,
            ),
            // КПП
            array(
                'key' => 'field_kpp',
                'label' => 'КПП',
                'name' => 'kpp',
                'type' => 'text',
                'required' => 0,
            ),
            // Расчетный счет
            array(
                'key' => 'field_rs',
                'label' => 'Расчетный счет',
                'name' => 'rs',
                'type' => 'text',
                'required' => 0,
            ),
            // БИК
            array(
                'key' => 'field_bik',
                'label' => 'БИК',
                'name' => 'bik',
                'type' => 'text',
                'required' => 0,
            ),
            // Корреспондентский счет
            array(
                'key' => 'field_ks',
                'label' => 'Корреспондентский счет',
                'name' => 'ks',
                'type' => 'text',
                'required' => 0,
            ),

            // ============================================================
            // ВКЛАДКА: Контактная информация
            // ============================================================
            array(
                'key' => 'field_tab_contacts',
                'label' => 'Контактная информация',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
                'endpoint' => 0,
            ),
            // Email
            array(
                'key' => 'field_email',
                'label' => 'Email',
                'name' => 'email',
                'type' => 'text',
                'required' => 0,
            ),
            // Телефон
            array(
                'key' => 'field_phone',
                'label' => 'Телефон',
                'name' => 'phone',
                'type' => 'text',
                'required' => 0,
            ),
            // Время работы
            array(
                'key' => 'field_working_hours',
                'label' => 'Время работы',
                'name' => 'working_hours',
                'type' => 'text',
                'required' => 0,
            ),
            // Широта
            array(
                'key' => 'field_latitude',
                'label' => 'Широта',
                'name' => 'latitude',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Пример: 55.751244',
                'wrapper' => array('width' => '50'),
            ),
            // Долгота
            array(
                'key' => 'field_longitude',
                'label' => 'Долгота',
                'name' => 'longitude',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Пример: 37.618423',
                'wrapper' => array('width' => '50'),
            ),
            // Google карты
            array(
                'key' => 'field_google_maps',
                'label' => 'Google Maps',
                'name' => 'google_maps',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Ссылка на организацию в Google Maps',
            ),
            // Яндекс карты
            array(
                'key' => 'field_yandex',
                'label' => 'Яндекс карты',
                'name' => 'yandex',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Ссылка на организацию в Яндекс.Картах',
            ),
            // 2GIS
            array(
                'key' => 'field_2gis',
                'label' => '2GIS',
                'name' => '2gis',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Ссылка на организацию в 2ГИС',
            ),

            // ============================================================
            // ВКЛАДКА: Социальные сети
            // ============================================================
            array(
                'key' => 'field_tab_social',
                'label' => 'Социальные сети',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
                'endpoint' => 0,
            ),
            // ВКонтакте
            array(
                'key' => 'field_vk',
                'label' => 'ВКонтакте',
                'name' => 'vk',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Ссылка на группу или профиль',
            ),
            // Telegram
            array(
                'key' => 'field_telegram',
                'label' => 'Telegram',
                'name' => 'telegram',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Ссылка на канал или чат',
            ),
            // YouTube
            array(
                'key' => 'field_youtube',
                'label' => 'YouTube',
                'name' => 'youtube',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Ссылка на канал',
            ),
            // Rutube
            array(
                'key' => 'field_rutube',
                'label' => 'Rutube',
                'name' => 'rutube',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Ссылка на канал',
            ),
            // TikTok
            array(
                'key' => 'field_tiktok',
                'label' => 'TikTok',
                'name' => 'tiktok',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Ссылка на профиль',
            ),
            // Дзен
            array(
                'key' => 'field_dzen',
                'label' => 'Дзен',
                'name' => 'dzen',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Ссылка на канал в Яндекс.Дзен',
            ),
            // TenChat
            array(
                'key' => 'field_tenchat',
                'label' => 'TenChat',
                'name' => 'tenchat',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Ссылка на профиль',
            ),
            // MAX
            array(
                'key' => 'field_max',
                'label' => 'MAX',
                'name' => 'max',
                'type' => 'text',
                'required' => 0,
            ),

            // ============================================================
            // ВКЛАДКА: Интеграции
            // ============================================================
            array(
                'key' => 'field_tab_integrations',
                'label' => 'Интеграции',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
                'endpoint' => 0,
            ),

            // --- Яндекс.Метрика ---
            array(
                'key' => 'field_ym_counter',
                'label' => 'ID счетчика Яндекс.Метрики',
                'name' => 'ym_counter',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Оставьте пустым для отключения. Пример: 99296594',
            ),

            // --- VK Pixel (Top.Mail.Ru) ---
            array(
                'key' => 'field_vk_pixel',
                'label' => 'ID пикселя VK (Top.Mail.Ru)',
                'name' => 'vk_pixel',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Оставьте пустым для отключения. Пример: 3740103',
            ),

            // --- Подмена телефонов (Sourcebuster) ---
            array(
                'key' => 'field_phone_mapping',
                'label' => 'Подмена телефонов по источникам',
                'name' => 'phone_mapping',
                'type' => 'textarea',
                'required' => 0,
                'rows' => 6,
                'instructions' => 'Формат: источник:телефон (каждый с новой строки).<br>
<strong>default</strong> - телефон по умолчанию (обязателен).<br>
Источники: yandex, google, vk, facebook, instagram, telegram и др.<br>
Пример:<br>
<code>default:+7 (495) 111-11-11</code><br>
<code>yandex:+7 (495) 222-22-22</code><br>
<code>google:+7 (495) 333-33-33</code>',
                'default_value' => 'default:+7 (495) 000-00-00',
            ),

            // --- Telegram ---
            array(
                'key' => 'field_tg_bot_token',
                'label' => 'Telegram Bot Token',
                'name' => 'tg_bot_token',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Получить у @BotFather',
            ),
            array(
                'key' => 'field_tg_chat_id',
                'label' => 'Telegram Chat ID',
                'name' => 'tg_chat_id',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Получить у @userinfobot. Для групп начинается с -100',
            ),

            // --- Битрикс24 ---
            array(
                'key' => 'field_b24_webhook',
                'label' => 'Битрикс24 Webhook URL',
                'name' => 'b24_webhook',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Пример: https://your-domain.bitrix24.ru/rest/1/token/',
            ),
            array(
                'key' => 'field_b24_custom_fields',
                'label' => 'Маппинг пользовательских полей',
                'name' => 'b24_custom_fields',
                'type' => 'textarea',
                'required' => 0,
                'rows' => 10,
                'instructions' => 'Формат: имя_поля:UF_CRM_ID (каждое с новой строки).<br>
Доступные поля: page_url, site_domain, referer, responsible_id, ymcid, sbjs_first, sbjs_current, sbjs_udata<br>
Пример:<br>
<code>page_url:UF_CRM_1739778385613</code><br>
<code>site_domain:UF_CRM_1737881299253</code><br>
<code>ymcid:UF_CRM_1737881322910</code><br>
<code>sbjs_first:UF_CRM_1737884236235</code><br>
<code>sbjs_current:UF_CRM_1737884245367</code>',
            ),

            // ============================================================
            // ВКЛАДКА: SMTP
            // ============================================================
            array(
                'key' => 'field_tab_smtp',
                'label' => 'SMTP',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_smtp_host',
                'label' => 'SMTP сервер',
                'name' => 'smtp_host',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Пример: smtp.yandex.ru',
            ),
            array(
                'key' => 'field_smtp_port',
                'label' => 'SMTP порт',
                'name' => 'smtp_port',
                'type' => 'number',
                'required' => 0,
                'default_value' => 465,
                'instructions' => '465 для SSL, 587 для TLS',
            ),
            array(
                'key' => 'field_smtp_secure',
                'label' => 'Шифрование',
                'name' => 'smtp_secure',
                'type' => 'select',
                'required' => 0,
                'choices' => array(
                    'ssl' => 'SSL',
                    'tls' => 'TLS',
                ),
                'default_value' => 'ssl',
            ),
            array(
                'key' => 'field_smtp_username',
                'label' => 'SMTP логин',
                'name' => 'smtp_username',
                'type' => 'text',
                'required' => 0,
                'instructions' => 'Обычно email адрес',
            ),
            array(
                'key' => 'field_smtp_password',
                'label' => 'SMTP пароль',
                'name' => 'smtp_password',
                'type' => 'text',
                'required' => 0,
            ),
            array(
                'key' => 'field_smtp_from',
                'label' => 'Email отправителя',
                'name' => 'smtp_from',
                'type' => 'email',
                'required' => 0,
            ),
            array(
                'key' => 'field_smtp_from_name',
                'label' => 'Имя отправителя',
                'name' => 'smtp_from_name',
                'type' => 'text',
                'required' => 0,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'site-settings',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'left',
        'instruction_placement' => 'field',
    ));
}

/**
 * Хелпер-функции для получения настроек в шаблонах
 *
 * Используйте в шаблонах темы для быстрого доступа к настройкам:
 * echo get_site_setting('contact_email');
 * echo get_site_setting('phone_number', '+7 (999) 000-00-00'); // со значением по умолчанию
 */
function get_site_setting($field_name, $default = null) {
    if (!function_exists('get_field')) {
        return $default;
    }

    $value = get_field($field_name, 'options');
    return $value !== false && $value !== '' && $value !== null ? $value : $default;
}

/**
 * Вывод изображения логотипа
 *
 * Использование в шаблоне:
 * echo get_site_logo();
 * echo get_site_logo('medium'); // с определенным размером
 */
function get_site_logo($size = 'full') {
    if (!function_exists('get_field')) {
        return '';
    }

    $logo_id = get_field('site_logo', 'options');
    if ($logo_id) {
        return wp_get_attachment_image($logo_id, $size, false, array('alt' => get_bloginfo('name')));
    }

    return '';
}

/**
 * Получение URL логотипа
 *
 * Использование в шаблоне:
 * <img src="<?php echo get_site_logo_url(); ?>" alt="Logo">
 */
function get_site_logo_url($size = 'full') {
    if (!function_exists('get_field')) {
        return '';
    }

    $logo_id = get_field('site_logo', 'options');
    if ($logo_id) {
        $image = wp_get_attachment_image_src($logo_id, $size);
        return $image ? $image[0] : '';
    }

    return '';
}

/**
 * Получение всех социальных сетей
 *
 * Использование в шаблоне:
 * $social = get_social_links();
 * if (!empty($social['facebook'])) {
 *     echo '<a href="' . esc_url($social['facebook']) . '">Facebook</a>';
 * }
 */
function get_social_links() {
    if (!function_exists('get_field')) {
        return array();
    }

    $social = get_field('social_links', 'options');
    return is_array($social) ? $social : array();
}
