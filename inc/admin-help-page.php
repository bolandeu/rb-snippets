<?php
/**
 * RB Snippets - Admin Help Page with Tabs
 */

if (!defined('ABSPATH')) exit;

// Register admin menu
add_action('admin_menu', function() {
    add_options_page(
        'RB Snippets',
        'RB Snippets',
        'manage_options',
        'rb-snippets-help',
        'rb_snippets_help_page_html'
    );
});

function rb_snippets_help_page_html() {
    $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'shortcodes';
    ?>
    <div class="wrap">
        <h1>RB Snippets - Справка</h1>

        <nav class="nav-tab-wrapper">
            <a href="?page=rb-snippets-help&tab=shortcodes"
               class="nav-tab <?php echo $current_tab === 'shortcodes' ? 'nav-tab-active' : ''; ?>">
                Шорткоды
            </a>
            <a href="?page=rb-snippets-help&tab=analytics"
               class="nav-tab <?php echo $current_tab === 'analytics' ? 'nav-tab-active' : ''; ?>">
                Аналитика
            </a>
            <a href="?page=rb-snippets-help&tab=integrations"
               class="nav-tab <?php echo $current_tab === 'integrations' ? 'nav-tab-active' : ''; ?>">
                Интеграции CF7
            </a>
            <a href="?page=rb-snippets-help&tab=acf-shortcode"
               class="nav-tab <?php echo $current_tab === 'acf-shortcode' ? 'nav-tab-active' : ''; ?>">
                ACF [sf]
            </a>
            <a href="?page=rb-snippets-help&tab=rest-api"
               class="nav-tab <?php echo $current_tab === 'rest-api' ? 'nav-tab-active' : ''; ?>">
                REST API
            </a>
            <a href="?page=rb-snippets-help&tab=acf-settings"
               class="nav-tab <?php echo $current_tab === 'acf-settings' ? 'nav-tab-active' : ''; ?>">
                ACF Settings
            </a>
        </nav>

        <div class="tab-content" style="margin-top: 20px;">
            <?php
            switch ($current_tab) {
                case 'analytics':
                    rb_snippets_tab_analytics();
                    break;
                case 'integrations':
                    rb_snippets_tab_integrations();
                    break;
                case 'acf-shortcode':
                    rb_snippets_tab_acf_shortcode();
                    break;
                case 'rest-api':
                    rb_snippets_tab_rest_api();
                    break;
                case 'acf-settings':
                    rb_snippets_tab_acf_settings();
                    break;
                default:
                    rb_snippets_tab_shortcodes();
                    break;
            }
            ?>
        </div>
    </div>
    <?php
}

/**
 * Tab: Shortcodes (domain, url, page_content)
 */
function rb_snippets_tab_shortcodes() {
    ?>
    <h2>[domain] - Вывод домена сайта</h2>
    <table class="widefat fixed striped" style="margin-bottom: 30px;">
        <thead>
            <tr>
                <th style="width: 35%;">Пример</th>
                <th>Описание</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>[domain]</code></td>
                <td>Выводит домен сайта текстом: <code>example.com</code></td>
            </tr>
            <tr>
                <td><code>[domain link="true"]</code></td>
                <td>Выводит кликабельную ссылку на главную</td>
            </tr>
            <tr>
                <td><code>[domain link="true" text="На главную"]</code></td>
                <td>Ссылка с кастомным текстом</td>
            </tr>
            <tr>
                <td><code>[domain link="true" path="/contacts/"]</code></td>
                <td>Ссылка на конкретную страницу</td>
            </tr>
        </tbody>
    </table>

    <h2>[url] - Вывод URL сайта</h2>
    <table class="widefat fixed striped" style="margin-bottom: 30px;">
        <thead>
            <tr>
                <th style="width: 35%;">Пример</th>
                <th>Описание</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>[url]</code></td>
                <td>Выводит полный URL главной: <code>https://example.com</code></td>
            </tr>
            <tr>
                <td><code>[url path="/privacy-policy/"]</code></td>
                <td>URL конкретной страницы: <code>https://example.com/privacy-policy/</code></td>
            </tr>
            <tr>
                <td><code>[url path="/contacts/" link="true"]</code></td>
                <td>Кликабельная ссылка на страницу</td>
            </tr>
            <tr>
                <td><code>[url path="/contacts/" link="true" text="Контакты"]</code></td>
                <td>Ссылка с кастомным текстом</td>
            </tr>
        </tbody>
    </table>

    <h2>[page_content] - Вставка контента страницы</h2>
    <p>Позволяет вставить содержимое одной страницы в другую.</p>
    <table class="widefat fixed striped" style="margin-bottom: 30px;">
        <thead>
            <tr>
                <th style="width: 35%;">Пример</th>
                <th>Описание</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>[page_content id="12"]</code></td>
                <td>Выводит <strong>весь</strong> контент страницы с ID 12</td>
            </tr>
            <tr>
                <td><code>[page_content slug="about-us"]</code></td>
                <td>Выводит контент по <strong>ярлыку</strong> (slug) страницы</td>
            </tr>
            <tr>
                <td><code>[page_content id="12" block="1"]</code></td>
                <td>Выводит только <strong>первый блок</strong> (верхнего уровня)</td>
            </tr>
            <tr>
                <td><code>[page_content id="12" block="2-4"]</code></td>
                <td>Выводит <strong>диапазон блоков</strong> со 2-го по 4-й</td>
            </tr>
        </tbody>
    </table>
    <p><em>Примечание: Код автоматически игнорирует пустые блоки и переносы строк.</em></p>
    <?php
}

/**
 * Tab: Analytics (Yandex.Metrika, Sourcebuster, UTM)
 */
function rb_snippets_tab_analytics() {
    ?>
    <h2>Яндекс.Метрика</h2>
    <p>Файл: <code>inc/tag-manager.php</code></p>

    <h3>Настройка</h3>
    <pre style="background: #f5f5f5; padding: 15px;">$ywm_counter = '99296594';  // ID счетчика (оставьте пустым для отключения)</pre>

    <h3>Что делает</h3>
    <ul>
        <li>Устанавливает код счетчика в <code>&lt;head&gt;</code></li>
        <li>Отслеживает клики по <code>mailto:</code> и <code>tel:</code> ссылкам</li>
        <li>Отслеживает копирование email и телефонов</li>
        <li>Отслеживает отправку форм Contact Form 7</li>
        <li>Добавляет скрытые поля в формы: <code>ymcid</code>, <code>sbjs_*</code></li>
    </ul>

    <h3>Цели Яндекс.Метрики</h3>
    <table class="widefat fixed striped" style="margin-bottom: 30px;">
        <thead>
            <tr>
                <th>Цель</th>
                <th>Когда срабатывает</th>
            </tr>
        </thead>
        <tbody>
            <tr><td><code>FORM_SENT</code></td><td>Успешная отправка формы CF7</td></tr>
            <tr><td><code>EMAIL_CLICK</code></td><td>Клик по ссылке mailto:</td></tr>
            <tr><td><code>EMAIL_COPY</code></td><td>Копирование текста с email</td></tr>
            <tr><td><code>PHONE_CLICK</code></td><td>Клик по ссылке tel:</td></tr>
            <tr><td><code>PHONE_COPY</code></td><td>Копирование текста с телефоном</td></tr>
        </tbody>
    </table>

    <hr style="margin: 30px 0;">

    <h2>Sourcebuster.js</h2>
    <p>Файл: <code>inc/sourcebuster/sourcebuster.php</code></p>
    <p>Библиотека для определения источника трафика и сохранения в cookies.</p>

    <h3>Настройка подмены телефонов</h3>
    <pre style="background: #f5f5f5; padding: 15px;">$rb_phone_default = '+7 (495) 275-30-85';  // Телефон по умолчанию
$rb_phone_yandex  = '+7 (495) 275-30-88';  // Телефон для трафика из Яндекса</pre>

    <h3>Как работает подмена</h3>
    <ul>
        <li>Скрипт ищет элементы с классом <code>.phone</code></li>
        <li>Если источник трафика = yandex, заменяет на <code>$rb_phone_yandex</code></li>
        <li>Автоматически обновляет <code>href="tel:..."</code></li>
    </ul>

    <h3>Cookies Sourcebuster</h3>
    <table class="widefat fixed striped" style="margin-bottom: 30px;">
        <thead>
            <tr>
                <th>Cookie</th>
                <th>Описание</th>
            </tr>
        </thead>
        <tbody>
            <tr><td><code>sbjs_first</code></td><td>Первый источник посещения</td></tr>
            <tr><td><code>sbjs_current</code></td><td>Текущий источник (последний)</td></tr>
            <tr><td><code>sbjs_udata</code></td><td>Данные о пользователе</td></tr>
        </tbody>
    </table>

    <hr style="margin: 30px 0;">

    <h2>UTM Tracker</h2>
    <p>Файл: <code>inc/utm-tracker.php</code></p>
    <p>Сохраняет UTM-метки из URL в сессию и cookies на 30 дней.</p>

    <h3>Использование в PHP</h3>
    <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">$utm = rb_get_utm_params();

// Результат:
[
    'UTM_SOURCE'   => 'yandex',
    'UTM_MEDIUM'   => 'cpc',
    'UTM_CAMPAIGN' => 'sale_2024',
    'UTM_CONTENT'  => 'banner1',
    'UTM_TERM'     => 'купить товар'
]</pre>

    <h3>Приоритет получения</h3>
    <ol>
        <li><code>$_GET</code> - из текущего URL</li>
        <li><code>$_SESSION</code> - из сессии</li>
        <li><code>$_COOKIE</code> - из cookies</li>
    </ol>
    <?php
}

/**
 * Tab: Integrations (CF7 → Bitrix24, Telegram)
 */
function rb_snippets_tab_integrations() {
    ?>
    <h2>Contact Form 7 → Битрикс24</h2>
    <p>Файл: <code>inc/cf7-bitrix24.php</code></p>
    <p>Автоматическая отправка заявок из CF7 в Битрикс24 CRM как лиды.</p>

    <h3>Настройка</h3>
    <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">// Webhook URL Битрикс24 (получить в CRM → Интеграции → REST API)
$rb_b24_webhook = 'https://your-domain.bitrix24.ru/rest/1/your-token/';

// ID форм для исключения (не отправлять в B24)
$rb_b24_excluded_forms = array(123, 456);

// Маппинг пользовательских полей
$rb_b24_custom_fields = array(
    'page_url'    => 'UF_CRM_...',  // URL страницы
    'site_domain' => 'UF_CRM_...',  // Домен
    'ymcid'       => 'UF_CRM_...',  // Yandex Client ID
    'sbjs_first'  => 'UF_CRM_...',  // Sourcebuster first
    // ...
);</pre>

    <h3>Автоматически передаётся</h3>
    <table class="widefat fixed striped" style="margin-bottom: 30px;">
        <thead>
            <tr>
                <th>Поле B24</th>
                <th>Источник</th>
            </tr>
        </thead>
        <tbody>
            <tr><td><code>TITLE</code></td><td>"Заявка с сайта - {название сайта}"</td></tr>
            <tr><td><code>NAME</code></td><td>Поля: your-name, name, firstname</td></tr>
            <tr><td><code>PHONE</code></td><td>Поля: your-phone, phone, tel</td></tr>
            <tr><td><code>EMAIL</code></td><td>Поля: your-email, email</td></tr>
            <tr><td><code>COMMENTS</code></td><td>Сообщение + все доп. поля формы</td></tr>
            <tr><td><code>SOURCE_ID</code></td><td>Автоопределение: SEO/PPC/Referral</td></tr>
            <tr><td><code>UTM_*</code></td><td>UTM-метки (если подключен utm-tracker)</td></tr>
        </tbody>
    </table>

    <hr style="margin: 30px 0;">

    <h2>Contact Form 7 → Telegram</h2>
    <p>Файл: <code>inc/cf7-telegram.php</code></p>
    <p>Мгновенные уведомления о заявках в Telegram-бота.</p>

    <h3>Настройка</h3>
    <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">// Токен бота (получить у @BotFather)
$rb_tg_bot_token = '123456789:ABCdefGHIjklMNOpqrSTUvwxYZ';

// ID чата (получить у @userinfobot или @getidsbot)
$rb_tg_chat_id = '-1001234567890';  // Для группы с минусом

// ID форм для исключения
$rb_tg_excluded_forms = array();</pre>

    <h3>Как получить Chat ID</h3>
    <ol>
        <li>Создайте бота у <a href="https://t.me/BotFather" target="_blank">@BotFather</a></li>
        <li>Добавьте бота в группу или напишите ему</li>
        <li>Получите ID у <a href="https://t.me/userinfobot" target="_blank">@userinfobot</a> или <a href="https://t.me/getidsbot" target="_blank">@getidsbot</a></li>
        <li>Для групп ID начинается с <code>-100</code></li>
    </ol>

    <h3>Пример сообщения</h3>
    <pre style="background: #f5f5f5; padding: 15px;">📩 Новая заявка с сайта
━━━━━━━━━━━━━━━
🌐 Сайт: Example.com
📋 Форма: Обратный звонок
━━━━━━━━━━━━━━━
👤 Имя: Иван Петров
📱 Телефон: +7 999 123-45-67
✉️ Email: ivan@example.com
💬 Сообщение: Текст сообщения
━━━━━━━━━━━━━━━
🔗 Страница: https://example.com/contacts/
📈 Источник: yandex</pre>
    <?php
}

/**
 * Tab: ACF Shortcode [sf]
 */
function rb_snippets_tab_acf_shortcode() {
    ?>
    <h2>[sf] - ACF Shortcode</h2>
    <p><strong>sf</strong> = <strong>S</strong>ite <strong>F</strong>ield (поле сайта). Универсальный шорткод для вывода полей ACF.</p>
    <p><em>Файл: <code>inc/acf-shortcode.php</code> (требует раскомментирования)</em></p>

    <h3>Атрибуты</h3>
    <table class="widefat fixed striped" style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Атрибут</th>
                <th>Обязательный</th>
                <th>Описание</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>field</code></td>
                <td>Да</td>
                <td>Имя поля ACF</td>
            </tr>
            <tr>
                <td><code>format</code></td>
                <td>Нет</td>
                <td><code>digits_only</code> - только цифры (для tel: ссылок)</td>
            </tr>
            <tr>
                <td><code>where</code></td>
                <td>Нет</td>
                <td><code>post</code> - из текущего поста, пусто = из options</td>
            </tr>
            <tr>
                <td><code>id</code></td>
                <td>Нет</td>
                <td>ID конкретного поста</td>
            </tr>
            <tr>
                <td><code>prefix</code></td>
                <td>Нет</td>
                <td>Текст перед значением (только если поле не пустое)</td>
            </tr>
            <tr>
                <td><code>replace</code></td>
                <td>Нет</td>
                <td>Замена текста: <code>что|на_что</code></td>
            </tr>
        </tbody>
    </table>

    <h3>Примеры использования</h3>
    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 45%;">Пример</th>
                <th>Описание</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>[sf field="contact_email"]</code></td>
                <td>Email из настроек сайта</td>
            </tr>
            <tr>
                <td><code>[sf field="phone_number"]</code></td>
                <td>Телефон из настроек</td>
            </tr>
            <tr>
                <td><code>[sf field="phone_number" format="digits_only"]</code></td>
                <td>Только цифры: <code>79991234567</code></td>
            </tr>
            <tr>
                <td><code>[sf field="phone_number" prefix="Tel: "]</code></td>
                <td>С префиксом: <code>Tel: +7 (999) 123-45-67</code></td>
            </tr>
            <tr>
                <td><code>[sf field="custom_field" where="post"]</code></td>
                <td>Поле из текущего поста</td>
            </tr>
            <tr>
                <td><code>[sf field="author_name" id="123"]</code></td>
                <td>Поле из поста с ID 123</td>
            </tr>
            <tr>
                <td><code>[sf field="price" replace="руб.|RUB"]</code></td>
                <td>С заменой текста</td>
            </tr>
        </tbody>
    </table>

    <h3>Пример: Кликабельный телефон</h3>
    <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">&lt;a href="tel:[sf field='phone_number' format='digits_only']"&gt;
    [sf field="phone_number" prefix="&#128222; "]
&lt;/a&gt;</pre>

    <h3>Пример: WhatsApp кнопка</h3>
    <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">&lt;a href="https://wa.me/[sf field='phone_number' format='digits_only']"&gt;
    Написать в WhatsApp
&lt;/a&gt;</pre>
    <?php
}

/**
 * Tab: REST API Options
 */
function rb_snippets_tab_rest_api() {
    ?>
    <h2>REST API для ACF Options</h2>
    <p>Эндпоинт для получения и обновления настроек ACF через REST API.</p>
    <p><em>Файл: <code>inc/rest-api-extensions.php</code> (требует раскомментирования)</em></p>

    <h3>Базовый URL</h3>
    <pre style="background: #f5f5f5; padding: 15px;">/wp-json/siteoptions/v1/options</pre>

    <h3>GET - Получение настроек</h3>
    <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">curl -X GET 'https://example.com/wp-json/siteoptions/v1/options' \
  -u 'username:application_password'</pre>

    <h4>Пример ответа:</h4>
    <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">{
  "site_logo": 123,
  "contact_email": "info@example.com",
  "phone_number": "+7 (999) 123-45-67",
  "social_links": {
    "facebook": "https://facebook.com/example"
  }
}</pre>

    <h3>POST - Обновление настроек</h3>
    <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">curl -X POST 'https://example.com/wp-json/siteoptions/v1/options' \
  -H 'Content-Type: application/json' \
  -u 'username:application_password' \
  -d '{
    "contact_email": "new@example.com",
    "phone_number": "+7 (999) 999-99-99"
  }'</pre>

    <h3>JavaScript (Fetch)</h3>
    <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">fetch('/wp-json/siteoptions/v1/options', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': wpApiSettings.nonce
  },
  credentials: 'include',
  body: JSON.stringify({
    contact_email: 'new@example.com'
  })
});</pre>

    <h3>Требования</h3>
    <ul>
        <li>Пользователь должен быть авторизован</li>
        <li>Плагин ACF должен быть активен</li>
    </ul>
    <?php
}

/**
 * Tab: ACF Site Settings
 */
function rb_snippets_tab_acf_settings() {
    ?>
    <h2>ACF Site Settings</h2>
    <p>Автоматически создает страницу настроек сайта в админ-панели WordPress.</p>
    <p><em>Файл: <code>inc/acf-site-settings.php</code> (требует раскомментирования)</em></p>

    <h3>Страница в админке</h3>
    <pre style="background: #f5f5f5; padding: 15px;">WordPress Admin &rarr; Настройки сайта
URL: /wp-admin/admin.php?page=site-settings</pre>

    <h3>Добавление полей ACF</h3>
    <ol>
        <li>Перейдите в <strong>Группы полей</strong></li>
        <li>Создайте группу полей</li>
        <li>В разделе <strong>Расположение</strong> выберите:<br>
            Страница настроек &rarr; равно &rarr; <strong>Настройки сайта</strong></li>
    </ol>

    <h3>Хелпер-функции</h3>
    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 50%;">Функция</th>
                <th>Описание</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>get_site_setting('field_name', 'default')</code></td>
                <td>Получить значение настройки</td>
            </tr>
            <tr>
                <td><code>get_site_logo('medium')</code></td>
                <td>HTML-код логотипа</td>
            </tr>
            <tr>
                <td><code>get_site_logo_url('full')</code></td>
                <td>URL логотипа</td>
            </tr>
            <tr>
                <td><code>get_social_links()</code></td>
                <td>Массив ссылок на соцсети</td>
            </tr>
        </tbody>
    </table>

    <h3>Пример использования в теме</h3>
    <pre style="background: #f5f5f5; padding: 15px; overflow-x: auto;">&lt;?php
$email = get_site_setting('contact_email', 'info@example.com');
$phone = get_site_setting('phone_number');
$social = get_social_links();

if (!empty($social['facebook'])) {
    echo '&lt;a href="' . esc_url($social['facebook']) . '"&gt;Facebook&lt;/a&gt;';
}
?&gt;</pre>
    <?php
}
