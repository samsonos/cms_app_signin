<?php
/**
 * Created by PhpStorm.
 * User: nazarenko
 * Date: 20.10.2014
 * Time: 11:43
 */
namespace samson\cms\signin;
use samson\social\email\EmailStatus;
use samsonphp\event\Event;

/**
 * Generic class for user sign in
 * @author Olexandr Nazarenko <nazarenko@samsonos.com>
 * @copyright 2014 SamsonOS
 * @version 0.0.1
 */
class SignIn extends \samson\core\CompressableExternalModule
{

    public $id = 'signin';

    public static function authorize()
    {
        if (!m('social')->authorized()) {
            if (!m('socialemail')->cookieVerification()) {
                if (!url()->is('signin')) {
                    url()->redirect('signin');
                }
            } else {
                url()->redirect('signin');
            }
        } else {
            if (url()->is('signin')) {
                url()->redirect();
            }
        }
    }

    /** Check the user's authorization */
    public function __HANDLER()
    {
        self::authorize();
    }

    /** Main sign in template */
    public function __base()
    {
       // m('social')->prepare();
        s()->template('www/signin/signin_template.vphp');
        $result = '';
        $result .= m()->view('www/signin/signin_form.vphp')->output();
        m()->html($result)->title(t('Авторизация', true));
    }

    /** User asynchronous sign in */
    public function __async_login()
    {
        $user = null;
        $remember = false;
        $error = '';
        if (isset($_POST['email']) && isset($_POST['password'])) {
            $email = md5($_POST['email']);
            $password = md5($_POST['password']);
            if (isset($_POST['remember'])) {
                $remember = true;
            }
            $auth = m('socialemail')->authorizeWithEmail($email, $password, $remember, $user);
            if ($auth->code == EmailStatus::SUCCESS_EMAIL_AUTHORIZE) {
                if (dbQuery('user')->cond('user_id', $user->id)->first()) {
                    // Fire login success event
                    Event::fire('samson.cms.signin.login', array(& $user));
                    return array('status' => '1');
                } else {
                    $error .= m()->view('www/signin/signin_form.vphp')->errorClass('errorAuth')->output();
                    return array('status' => '0', 'html' => $error);
                }
            } else {
                $error .= m()->view('www/signin/signin_form.vphp')
                    ->errorClass('errorAuth')
                    ->userEmail("{$_POST['email']}")
                    ->output();
                return array('status' => '0', 'html' => $error);
            }
        } else {
            $error .= m()->view('www/signin/signin_form')->errorClass('errorAuth')->output();
            return array('status' => '0', 'html' => $error);
        }
    }

    /** User logout */
    public function __logout()
    {
        m('socialemail')->deauthorize();
        // Fire logout event
        Event::fire('samson.cms.signin.logout');
        url()->redirect();
    }

    /** Sending email with the correct address */
    public function __mail()
    {
        if (isset($_POST['email'])) {
            /** @var \samson\activerecord\user $user */
            $user = null;
            $result = '';
            if (dbQuery('user')->email($_POST['email'])->first($user)) {
                $user->confirmed = md5(generate_password(20) . time());
                $user->save();
                $message = m()->view('www/signin/email/pass_recovery')->code($user->confirmed)->output();

                mail_send($user->Email, 'info@samsonos.com', $message, t('Восстановление пароля!', true), 'SamsonCMS');

                $result .= m()->view('www/signin/pass_recovery_mailsend')->output();
                s()->template('www/signin/signin_template.vphp');
                m()->html($result)->title('Восстановление пароля');
            } else {
                url()->redirect();
            }
        } else {
            url()->redirect();
        }
    }

    /**
     * New password form
     * @param string $code Code password recovery
     */
    public function __confirm($code)
    {
        if (dbQuery('user')->confirmed($code)->first()) {
            $result = '';
            $result .= m()->view('www/signin/new_pass_form')->code($code)->output();
            s()->template('www/signin/signin_template.vphp');
            m()->html($result)->title('Восстановление пароля');
        } else {
            e404();
        }
    }

    /**
     * Setting new password and sign in
     * @param string $code Code password recovery
     */
    public function __recovery($code)
    {
        if (isset($_POST['password']) && isset($_POST['confirm_password'])
            && $_POST['password'] == $_POST['confirm_password']) {
            /** @var \samson\activerecord\user $user */
            $user = null;
            if (dbQuery('user')->confirmed($code)->first($user)) {
                $user->confirmed = 1;
                $user->md5_password = md5($_POST['password']);
                $user->Password = $_POST['password'];
                $user->save();
                if (m('socialemail')->authorizeWithEmail($user->md5_email, $user->md5_password, $user)
                                    ->code == EmailStatus::SUCCESS_EMAIL_AUTHORIZE) {
                    url()->redirect();
                }
            }
        } else {
            $result = '';
            $result .= m()->view('www/signin/pass_error')
                          ->message(t('Вы ввели некорректный пароль либо пароли не совпадают', true))
                          ->output();
            s()->template('www/signin/signin_template.vphp');
            m()->html($result)->title('Ошибка восстановление пароля');
        }
    }
}
