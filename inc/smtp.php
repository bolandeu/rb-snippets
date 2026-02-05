<?php

function custom_wp_mail_smtp( $phpmailer ) {
    $phpmailer->isSMTP();
    $phpmailer->Host       = 'smtp.server.com';    // Укажите ваш SMTP-сервер
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = 465;                   // Обычно 587 для TLS, 465 для SSL
    $phpmailer->SMTPSecure = 'ssl';                 // Тип шифрования: 'tls' или 'ssl'
    $phpmailer->Username   = 'noreply@somedomain.ru'; // SMTP-логин
    $phpmailer->Password   = 'somepassword';          // SMTP-пароль
    $phpmailer->From       = 'noreply@somedomain.ru'; // Адрес отправителя
    $phpmailer->FromName   = 'Some Name';         // Имя отправителя
}

// Подключаем настройку SMTP к системе почты WordPress
add_action( 'phpmailer_init', 'custom_wp_mail_smtp' );


add_filter( 'wpshop_contact_form_email_from', function() {
    return 'noreply@somedomain.ru'; // замените "вашдомен.ru" на свой
} );