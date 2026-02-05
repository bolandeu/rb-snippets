<?php
/**
 * RB Snippets - Yandex.Metrika Tag Manager
 *
 * Добавляет код счетчика Яндекс.Метрики и отслеживание событий
 */

if (!defined('ABSPATH')) exit;

/**
 * ID счетчика Яндекс.Метрики
 * Оставьте пустым, чтобы отключить вывод скриптов
 */
$ywm_counter = '99296594';

/**
 * Получить ID счетчика Яндекс.Метрики
 */
function rb_get_ym_counter() {
    global $ywm_counter;
    return $ywm_counter;
}

/**
 * Вывод кода счетчика Яндекс.Метрики в head
 */
function rb_ym_counter_head() {
    // Только на публичных страницах
    if (is_admin()) {
        return;
    }

    $ym_counter = rb_get_ym_counter();

    // Если ID пустой - не выводим ничего
    if (empty($ym_counter)) {
        return;
    }

    $ym_counter = esc_attr($ym_counter);
    ?>
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js', 'ym');

    ym(<?php echo $ym_counter; ?>, 'init', {
        clickmap: true,
        trackLinks: true,
        accurateTrackBounce: true,
        webvisor: true
    });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/<?php echo $ym_counter; ?>" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
    <?php
}
add_action('wp_head', 'rb_ym_counter_head', 1);

/**
 * Вывод скриптов отслеживания событий перед </body>
 */
function rb_ym_events_footer() {
    // Только на публичных страницах
    if (is_admin()) {
        return;
    }

    $ym_counter = rb_get_ym_counter();

    // Если ID пустой - не выводим ничего
    if (empty($ym_counter)) {
        return;
    }

    $ym_counter = esc_attr($ym_counter);
    ?>
<script type="text/javascript">
(function() {
    var ymCounter = <?php echo $ym_counter; ?>;

    // Проверяем что метрика инициализирована
    if (typeof ym !== 'function') {
        return;
    }

    /**
     * Отправка цели в Яндекс.Метрику
     */
    function sendYandexMetricaGoal(goalName, goalParams) {
        ym(ymCounter, 'getClientID', function(clientID) {
            goalParams.client_id = clientID;
            ym(ymCounter, 'reachGoal', goalName, goalParams);
        });
    }

    /**
     * Получение cookie
     */
    function getCookie(name) {
        var cookies = document.cookie.split(';');
        for (var i = 0; i < cookies.length; i++) {
            var cookie = cookies[i].trim();
            if (cookie.startsWith(name + '=')) {
                return cookie.substring(name.length + 1);
            }
        }
        return null;
    }

    // Отправка формы Contact Form 7
    document.addEventListener('wpcf7mailsent', function(event) {
        sendYandexMetricaGoal('FORM_SENT', {
            form_id: event.detail.contactFormId,
            page: window.location.pathname
        });
    }, false);

    // Копирование email
    document.addEventListener('copy', function(event) {
        var selectedText = window.getSelection().toString();
        var emailRegex = /[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/;

        if (emailRegex.test(selectedText)) {
            sendYandexMetricaGoal('EMAIL_COPY', {
                email: selectedText,
                page: window.location.pathname
            });
        }
    });

    // Клик по email
    document.querySelectorAll('a[href^="mailto:"]').forEach(function(emailLink) {
        emailLink.addEventListener('click', function(event) {
            var email = this.href.replace('mailto:', '');
            sendYandexMetricaGoal('EMAIL_CLICK', {
                email: email,
                page: window.location.pathname
            });
        });
    });

    // Клик по номеру телефона
    document.querySelectorAll('a[href^="tel:"]').forEach(function(phoneLink) {
        phoneLink.addEventListener('click', function(event) {
            var phoneNumber = this.href.replace('tel:', '');
            sendYandexMetricaGoal('PHONE_CLICK', {
                phone: phoneNumber,
                page: window.location.pathname
            });
        });
    });

    // Копирование номера телефона
    document.addEventListener('copy', function(event) {
        var selectedText = window.getSelection().toString();
        var phoneRegex = /(\+7|8)[\s(]?\d{3}[)\s]?\d{3}[\s-]?\d{2}[\s-]?\d{2}/;

        if (phoneRegex.test(selectedText)) {
            sendYandexMetricaGoal('PHONE_COPY', {
                phone: selectedText,
                page: window.location.pathname
            });
        }
    });

    // Добавление скрытых полей в формы (sourcebuster cookies + YM Client ID)
    document.querySelectorAll('form').forEach(function(form) {
        // sbjs_first
        var firstField = form.querySelector('[name="sbjs_first"]');
        if (!firstField) {
            firstField = document.createElement('input');
            firstField.type = 'hidden';
            firstField.name = 'sbjs_first';
            form.appendChild(firstField);
        }
        firstField.value = getCookie('sbjs_first') || '';

        // sbjs_current
        var currentField = form.querySelector('[name="sbjs_current"]');
        if (!currentField) {
            currentField = document.createElement('input');
            currentField.type = 'hidden';
            currentField.name = 'sbjs_current';
            form.appendChild(currentField);
        }
        currentField.value = getCookie('sbjs_current') || '';

        // sbjs_udata
        var udataField = form.querySelector('[name="sbjs_udata"]');
        if (!udataField) {
            udataField = document.createElement('input');
            udataField.type = 'hidden';
            udataField.name = 'sbjs_udata';
            form.appendChild(udataField);
        }
        udataField.value = getCookie('sbjs_udata') || '';

        // ymcid (Yandex Metrika Client ID)
        var ymcidField = form.querySelector('[name="ymcid"]');
        if (!ymcidField) {
            ymcidField = document.createElement('input');
            ymcidField.type = 'hidden';
            ymcidField.name = 'ymcid';
            form.appendChild(ymcidField);
        }
        // Замыкание для сохранения ссылки на поле
        (function(field) {
            ym(ymCounter, 'getClientID', function(clientID) {
                if (clientID) {
                    field.value = clientID;
                }
            });
        })(ymcidField);
    });
})();
</script>
    <?php
}
add_action('wp_footer', 'rb_ym_events_footer', 99);
