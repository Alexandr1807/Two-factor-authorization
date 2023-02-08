<?php

function fn_generate_verification_code($user_id) {
    $str = '0123456789abcdefghijklmnopqrstuvwxyz';
    $update_data = [
        'verify_code' => substr(str_shuffle($str), 0, 6),
    ];
    db_query("UPDATE ?:users SET ?u WHERE user_id = ?i", $update_data, $user_id);
    return $update_data['verify_code'];
}

function fn_get_user_data($user_id) {
    $user_data = db_get_row("SELECT * FROM ?:users WHERE user_id = ?i", $user_id);
    return $user_data;
}

function fn_send_verification_code($email) {
    $verification_code = db_get_field("SELECT verify_code FROM ?:users WHERE email = ?s", $email);

    /** @var \Tygh\Mailer\Mailer $mailer */
    $mailer = Tygh::$app['mailer'];

    $mailer->send([
        'to'            => $email,
        'from'          => 'default_company_users_department',
        'data'          => [
            'password'       => $verification_code
        ],
        'tpl'           => 'addons/sd_two_factor_auth/verify_code.tpl',
    ], AREA);
}

function fn_delete_verification_code($user_id) {
    db_get_field("UPDATE `?:users` SET `verify_code`=NULL WHERE user_id = ?i", $user_id);
}
