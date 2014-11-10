<?php
/**
 * Created by PhpStorm.
 * User: nazarenko
 * Date: 20.10.2014
 * Time: 11:43
 */
namespace samson\cms\signin;
use samson\social\email\EmailStatus;

/**   */
class SignIn extends \samson\core\CompressableExternalModule
{

    public $id = 'signin';

    /** Check the user's authorization */
    public function __HANDLER()
    {
        if (!m('social')->authorized()) {
            if (!url()->is('signin')) {
                url()->redirect('signin');
            }
        }
    }

    /** Main sign in template */
    public function __base()
    {
        s()->template('app/view/signin/signin_template.php');
        $result = '';
        $result .= m()->view('signin/signin_form.php')->output();
        m()->html($result)->title('Авторизация');
    }

    /** User asynchronous sign in */
    public function __async_login()
    {
        $user = null;
        $error = '';
        if (isset($_POST['email']) && isset($_POST['password'])) {
            $email = md5($_POST['email']);
            $password = md5($_POST['password']);
            $auth = m('socialemail')->authorizeWithEmail($email, $password, $user);
            if ($auth->code == EmailStatus::SUCCESS_EMAIL_AUTHORIZE) {
                if (dbQuery('user')->cond('UserID', $user->id)->first()) {
                    return array('status' => '1');
                } else {
                    $error .= m()->view('signin/signin_form.php')->errorClass('errorAuth')->output();
                    return array('status' => '0', 'html' => $error);
                }
            } else {
                $error .= m()->view('signin/signin_form.php')
                    ->errorClass('errorAuth')
                    ->userEmail("{$_POST['email']}")
                    ->output();
                return array('status' => '0', 'html' => $error);
            }
        } else {
            $error .= m()->view('signin/signin_form')->errorClass('errorAuth')->output();
            return array('status' => '0', 'html' => $error);
        }
    }

    /** User logout */
    public function __logout()
    {
        m('socialemail')->deauthorize();
        url()->redirect();
    }

    /** Main password recovery template */
    public function __passrecovery()
    {
        $result = '';
        $result .= m()->view('signin/pass_recovery_form')->output();
        s()->template('app/view/signin/signin_template.php');
        m()->html($result)->title('Восстановление пароля');
    }

    /** Sending email with the correct address */
    public function __mail()
    {
        if (isset($_POST['email'])) {
            /** @var \samson\activerecord\user $user */
            $user = null;
            $result = '';
            if (dbQuery('user')->Email($_POST['email'])->first($user)) {
                $user->confirmed = md5(generate_password(20) . time());
                $user->save();
                $message = m()->view('signin/email/pass_recovery')->code($user->confirmed)->output();

                mail_send($user->Email, 'info@samsonos.com', $message, t('Восстановление пароля!', true), 'SamsonCMS');

                $result .= m()->view('signin/pass_recovery_mailsend')->output();
                s()->template('app/view/signin/signin_template.php');
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
            $result .= m()->view('signin/new_pass_form')->code($code)->output();
            s()->template('app/view/signin/signin_template.php');
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
        if (isset($_POST['password']) && isset($_POST['confirm_password']) && $_POST['password'] == $_POST['confirm_password']) {
            /** @var \samson\activerecord\user $user */
            $user = null;
            if (dbQuery('user')->confirmed($code)->first($user)) {
                $user->confirmed = 1;
                $user->md5_password = md5($_POST['password']);
                $user->Password = $_POST['password'];
                $user->save();
                if (m('socialemail')->authorizeWithEmail($user->md5_email, $user->md5_password, $user)->code == EmailStatus::SUCCESS_EMAIL_AUTHORIZE) {
                    url()->redirect();
                }
            }
        } else {
            $result = '';
            $result .= m()->view('signin/pass_error')->message(t('Вы ввели некорректный пароль либо пароли не совпадают', true))->output();
            s()->template('app/view/signin/signin_template.php');
            m()->html($result)->title('Ошибка восстановление пароля');
        }
    }
}
