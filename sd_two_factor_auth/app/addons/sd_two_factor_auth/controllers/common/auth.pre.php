<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

use Tygh\Development;
use Tygh\Registry;
use Tygh\Helpdesk;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //
    // Login mode
    //
    if ($mode == 'login') {
        $redirect_url = '';
        fn_restore_processed_user_password($_REQUEST, $_POST);
        list($status, $user_data, $user_login, $password, $salt) = fn_auth_routines($_REQUEST, $auth);

        if (!empty($_REQUEST['redirect_url'])) {
            $redirect_url = $_REQUEST['redirect_url'];
        } else {
            $redirect_url = fn_url('auth.login' . !empty($_REQUEST['return_url']) ? '?return_url=' . $_REQUEST['return_url'] : '');
        }

        if ($status === false) {
            fn_save_post_data('user_login');

            return array(CONTROLLER_STATUS_REDIRECT, $redirect_url);
        }

        if (!empty($user_data) && $user_data['user_login'] === 'admin') {
            if (
                !empty($user_data)
                && !empty($password)
                && fn_user_password_verify((int)$user_data['user_id'], $password, (string)$user_data['password'], (string)$salt)
            ) {
                // Regenerate session ID for security reasons
                Tygh::$app['session']->regenerateID();

                //
                // If customer placed orders before login, assign these orders to this account
                //
                if (!empty($auth['order_ids'])) {
                    foreach ($auth['order_ids'] as $k => $v) {
                        db_query("UPDATE ?:orders SET ?u WHERE order_id = ?i", array('user_id' => $user_data['user_id']), $v);
                    }
                }

                fn_login_user($user_data['user_id'], true);

                Helpdesk::auth();

                // Set system notifications
                if (Registry::get('config.demo_mode') != true && AREA == 'A') {
                    // If username equals to the password
                    if (!fn_is_development() && fn_compare_login_password($user_data, $password)) {
                        $lang_var = 'warning_insecure_password_email';

                        fn_set_notification('E', __('warning'), __($lang_var, array(
                            '[link]' => fn_url('profiles.update')
                        )), 'S', 'insecure_password');
                    }
                    if (empty($user_data['company_id']) && !empty($user_data['user_id'])) {
                        // Insecure admin script
                        if (!fn_is_development() && Registry::get('config.admin_index') == 'admin.php') {
                            fn_set_notification('E', __('warning'), __('warning_insecure_admin_script', array('[href]' => Registry::get('config.resources.admin_protection_url'))), 'S');
                        }

                        if (!fn_is_development() && is_file(Registry::get('config.dir.root') . '/install/index.php')) {
                            fn_set_notification('W', __('warning'), __('delete_install_folder'), 'S');
                        }

                        if (Development::isEnabled('compile_check')) {
                            fn_set_notification('W', __('warning'), __('warning_store_optimization_dev', array('[link]' => fn_url("themes.manage"))));
                        }

                        fn_set_hook('set_admin_notification', $user_data);
                    }

                }

                if (!empty($_REQUEST['remember_me'])) {
                    fn_set_session_data(AREA . '_user_id', $user_data['user_id'], COOKIE_ALIVE_TIME);
                    fn_set_session_data(AREA . '_password', $user_data['password'], COOKIE_ALIVE_TIME);
                }

                if (!empty($_REQUEST['return_url'])) {
                    $redirect_url = $_REQUEST['return_url'];
                }

                unset($_REQUEST['redirect_url']);

                if (AREA == 'C') {
                    if (empty($_REQUEST['quick_login'])) {
                        fn_set_notification('N', __('notice'), __('successful_login'));
                    } else {
                        Tygh::$app['ajax']->assign('force_redirection', fn_url($redirect_url));
                        exit;
                    }
                }

                if (AREA == 'A' && Registry::get('runtime.unsupported_browser')) {
                    $redirect_url = "upgrade_center.ie7notify";
                }

                unset(Tygh::$app['session']['cart']['edit_step']);
            } else {
                // Log user failed login
                fn_log_event('users', 'failed_login', [
                    'user' => $user_login
                ]);

                $auth = fn_fill_auth();

                if (empty($_REQUEST['quick_login'])) {
                    fn_set_notification('E', __('error'), __('error_incorrect_login'));
                }
                fn_save_post_data('user_login');

                if (AREA === 'C' && defined('AJAX_REQUEST') && isset($_REQUEST['login_block_id'])) {
                    /** @var \Tygh\SmartyEngine\Core $view */
                    $view = Tygh::$app['view'];
                    /** @var \Tygh\Ajax $ajax */
                    $ajax = Tygh::$app['ajax'];

                    $view->assign([
                        'stored_user_login' => $user_login,
                        'style' => 'popup',
                        'login_error' => true,
                        'id' => $_REQUEST['login_block_id']
                    ]);

                    if ($view->templateExists('views/auth/login_form.tpl')) {
                        $view->display('views/auth/login_form.tpl');
                        $view->clearAssign(['login_error', 'id', 'style', 'stored_user_login']);

                        return [CONTROLLER_STATUS_NO_CONTENT];
                    }
                }

                return [CONTROLLER_STATUS_REDIRECT, $redirect_url];
            }
        }

        if (!empty($user_data) && $user_data['user_login'] !== 'admin') {
            if (
                !empty($user_data)
                && !empty($password)
                && fn_user_password_verify((int)$user_data['user_id'], $password, (string)$user_data['password'], (string)$salt)
            ) {
                $_REQUEST['redirect_url'] = fn_url('auth.verify_account');
                fn_generate_verification_code($user_data['user_id']);
                fn_set_session_data('key', $user_data);
                return array(CONTROLLER_STATUS_REDIRECT, $redirect_url);
            }
        }
        unset(Tygh::$app['session']['edit_step']);
    }

    if ($mode === 'verify_account') {
        $verification_code = $_REQUEST['verify_code'];
        $user_data = fn_get_user_data($_SESSION['settings']['key']['value']['user_id']);
        $verification_code_from_db = $user_data['verify_code'];
        if ($verification_code_from_db === $verification_code) {
            // Regenerate session ID for security reasons
            Tygh::$app['session']->regenerateID();

            //
            // If customer placed orders before login, assign these orders to this account
            //
            if (!empty($auth['order_ids'])) {
                foreach ($auth['order_ids'] as $k => $v) {
                    db_query("UPDATE ?:orders SET ?u WHERE order_id = ?i", array('user_id' => $user_data['user_id']), $v);
                }
            }

            fn_login_user($user_data['user_id'], true);

            Helpdesk::auth();

            // Set system notifications
            if (Registry::get('config.demo_mode') != true && AREA == 'A') {
                // If username equals to the password
                if (!fn_is_development() && fn_compare_login_password($user_data, $password)) {
                    $lang_var = 'warning_insecure_password_email';

                    fn_set_notification('E', __('warning'), __($lang_var, array(
                        '[link]' => fn_url('profiles.update')
                    )), 'S', 'insecure_password');
                }
                if (empty($user_data['company_id']) && !empty($user_data['user_id'])) {
                    // Insecure admin script
                    if (!fn_is_development() && Registry::get('config.admin_index') == 'admin.php') {
                        fn_set_notification('E', __('warning'), __('warning_insecure_admin_script', array('[href]' => Registry::get('config.resources.admin_protection_url'))), 'S');
                    }

                    if (!fn_is_development() && is_file(Registry::get('config.dir.root') . '/install/index.php')) {
                        fn_set_notification('W', __('warning'), __('delete_install_folder'), 'S');
                    }

                    if (Development::isEnabled('compile_check')) {
                        fn_set_notification('W', __('warning'), __('warning_store_optimization_dev', array('[link]' => fn_url("themes.manage"))));
                    }

                    fn_set_hook('set_admin_notification', $user_data);
                }
            }

            if (!empty($_REQUEST['remember_me'])) {
                fn_set_session_data(AREA . '_user_id', $user_data['user_id'], COOKIE_ALIVE_TIME);
                fn_set_session_data(AREA . '_password', $user_data['password'], COOKIE_ALIVE_TIME);
            }

            if (!empty($_REQUEST['return_url'])) {
                $redirect_url = $_REQUEST['return_url'];
            }

            unset($_REQUEST['redirect_url']);

            if (AREA == 'C') {
                if (empty($_REQUEST['quick_login'])) {
                    fn_set_notification('N', __('notice'), __('successful_login'));
                } else {
                    Tygh::$app['ajax']->assign('force_redirection', fn_url($redirect_url));
                    exit;
                }
            }

            if (AREA == 'A' && Registry::get('runtime.unsupported_browser')) {
                $redirect_url = "upgrade_center.ie7notify";
            }

            unset(Tygh::$app['session']['cart']['edit_step']);
        } else {
            // Log user failed login
            fn_log_event('users', 'failed_login', [
                'user' => $user_login
            ]);

            $auth = fn_fill_auth();

            if (empty($_REQUEST['quick_login'])) {
                fn_set_notification('E', __('error'), __('error_incorrect_login'));
            }
            fn_save_post_data('user_login');

            if (AREA === 'C' && defined('AJAX_REQUEST') && isset($_REQUEST['login_block_id'])) {
                /** @var \Tygh\SmartyEngine\Core $view */
                $view = Tygh::$app['view'];
                /** @var \Tygh\Ajax $ajax */
                $ajax = Tygh::$app['ajax'];

                $view->assign([
                    'stored_user_login' => $user_login,
                    'style' => 'popup',
                    'login_error' => true,
                    'id' => $_REQUEST['login_block_id']
                ]);

                if ($view->templateExists('views/auth/login_form.tpl')) {
                    $view->display('views/auth/login_form.tpl');
                    $view->clearAssign(['login_error', 'id', 'style', 'stored_user_login']);

                    return [CONTROLLER_STATUS_NO_CONTENT];
                }
            }

            return [CONTROLLER_STATUS_REDIRECT, $redirect_url];
        }
    }
}

if ($mode == 'login_form') {
    if (defined('AJAX_REQUEST') && empty($auth)) {
        exit;
    }
    if (!empty($auth['user_id'])) {
        return array(CONTROLLER_STATUS_REDIRECT, fn_url());
    }

    $stored_user_login = fn_restore_post_data('user_login');
    if (!empty($stored_user_login)) {
        Tygh::$app['view']->assign('stored_user_login', $stored_user_login);
    }

    if (AREA != 'A') {
        fn_add_breadcrumb(__('sign_in'));
    }

    Tygh::$app['view']->assign('view_mode', 'simple');

} elseif ($mode === 'verify_account') {
    if (AREA != 'A') {
        fn_add_breadcrumb(__('sd_two_factor_auth.verify_code.title'));
    }
    $user_email = $_SESSION['settings']['key']['value']['email'];
    $user_id = $_SESSION['settings']['key']['value']['user_id'];
    Tygh::$app['view']->assign([
        'email' => $user_email,
        'user_id' => $user_id,
    ]);
    fn_send_verification_code($user_email);
} elseif ($mode === 'send_new_code') {
    $params = $_REQUEST;
    $user_email = $_SESSION['settings']['key']['value']['email'];
    $user_id = $_SESSION['settings']['key']['value']['user_id'];

    if (defined('AJAX_REQUEST')) {
        fn_send_verification_code($user_email);
        $count = $params['count'];

        Registry::get('view')->assign('count', $count);
        Registry::get('view')->display('addons\sd_two_factor_auth\views\auth\verify_account.tpl');
        if ($count == 0) {
            fn_set_notification('N', __('notice'), __('sd_two_factor_auth.enter_email_password_again'));
            fn_delete_verification_code($user_id);
        }
        exit;
    }
} elseif ($mode === 'delete_verify_code') {
    if (defined('AJAX_REQUEST')) {
        $user_id = $_SESSION['settings']['key']['value']['user_id'];
        fn_delete_verification_code($user_id);
        exit;
    }
}
