<?php
namespace samson\cms\signin;

use samsonphp\i18n\IDictionary;

class Dictionary implements IDictionary
{
    public function getDictionary()
    {
        return array(
            "en"	=>array(
                "Авторизация" => "Sign in",
                "Запомнить меня" => "Remember me",
                "Восстановление пароля" => "Recovery password",
                "Отправить" => "Send",
                "Ошибка" => "Error",
                "Вернуться" => "Back",
                "Введите Ваш Email" => "Enter your email",
                "Еще раз новый пароль" => "New password again",
                "Попробовать снова" => "Try again",
                "Новый пароль" => "New password",
                "Пользователя с таким email адресом не существует. Проверьте Ваши данные или зарегистрируйтесь." => "Users with the email address does not exist. Check your data or register.",
                "Вам выслана ссылка на восстановление пароля" => "You will receive a link to the password recovery",
                "Вы ввели некорректный пароль либо пароли не совпадают" => "You have entered an incorrect password or passwords do not match",
                "В скором времени на указанную Вами почту придет письмо со ссылкой на восстановление пароля" => "In a short time you specified mail a letter with reference to the password recovery",
                "На введенный Вами email будет выслано сообщение с дальнейшими инструкциями." => "On your email will be sent a message with further instructions.",
            ),
            "ua"	=>array(
                "Авторизация" => "",
                "Запомнить меня" => "",
                "Восстановление пароля" => "",
                "Отправить" => "",
                "Ошибка" => "",
                "Вернуться" => "",
                "Введите Ваш Email" => "",
                "Еще раз новый пароль" => "",
                "Попробовать снова" => "",
                "Новый пароль" => "",
                "Пользователя с таким email адресом не существует. Проверьте Ваши данные или зарегистрируйтесь." => "",
                "Вам выслана ссылка на восстановление пароля" => "",
                "Вы ввели некорректный пароль либо пароли не совпадают" => "",
                "В скором времени на указанную Вами почту придет письмо со ссылкой на восстановление пароля" => "",
                "На введенный Вами email будет выслано сообщение с дальнейшими инструкциями." => "",
            ),
        );
    }
}
