<?php

namespace App\Application\Actions\Telegram;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class FAQAction extends BotAction
{
    public function __invoke(Nutgram $bot): void
    {
        $bot->sendMessage('⁉️');
        $message = <<<EOL
F.A.Q
 
*Что такое VPN и зачем он мне нужен?*
VPN — Виртуальная частная сеть, которая имитирует подключение из другой страны. VPN позволяет получить доступ к заблокированной информации и соц. сетям.
 
*Насколько безопасен ваш сервис?*
Мы не анализируем и не собираем ваши данные. Мы используем приложение Outline, технологии которого не позволяют нам отслеживать данные. Проверка в app сторах и открытый код программы это подтверждает.
 
*Сколько устройств я могу подключить?*
Мы не привязываемся к конкретным устройствам, и поэтому Вы можете использовать наш сервис на многих устройствах (домашний ПК, ноутбук, планшет, смартфон и т. д.).
 
*Как проверить работает ли VPN?*
Вы можете зайти на запрещенные в вашей стране сервисы или зайти на сайт 2ip и проверить из какой страны осуществлено подключение.
 
*Что делать если у меня не работает VPN?*
Для начала удостоверьтесь что у вас активирована подписка.  Для этого перейдите из главного меню в раздел “Мой тариф”. Если у вас есть активные тарифы, то попробуйте удалить текущий тип подключения в Outline, кликнув по троеточию и нажав “не показывать” и вновь добавьте сервер. Инструкцию по подключению можно найти в разделе “Полезные материалы” — “Инструкция по подключению”. В случае если это не помогло — обратитесь в техническую поддержку.
EOL;
        $bot->sendMessage(
            $message,
            [
                'parse_mode' => 'markdown',
                'reply_markup' => ReplyKeyboardMarkup::make(resize_keyboard: true, selective: true)
                    ->addRow(KeyboardButton::make('⁉️ Полезные материалы'))
            ]
        );
    }
}