<?php

namespace App\Services\Telegram\Commands;

use App\Services\MikBill\Admin\API;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class InputCommand extends Command
{
    public static function trigger(Update $update, TeleBot $bot)
    {
        return isset($update->message->text);
    }

    public function handle()
    {
        $update = $this->update;
        $bot = $this->bot;

        if ($this->getMenu() == 'menuSearchByLogin') {
            $this->menuSearchUser('login');
        }

        if ($this->getMenu() == 'menuSearchByUID') {
            $this->menuSearchUser('uid');
        }

        if ($this->getMenu() == 'menuSearchByDogovor') {
            $this->menuSearchUser('numdogovor');
        }

    }

    private function menuSearchUser($type = 'login')
    {
        $cabinet_host = config('services.mikbill.cabinet_host');

        // Ищем абона
        $api = new API();
        $users = $api->searchUsersMB($this->update->message->text, $type);

        if (!empty($users)) {

            foreach ($users as $user) {

                //Получим полную карточку абона
                $user = $api->getUserMB($user['useruid']);

                switch ($user['state']) {
                    case 1:
                        $status = 'обычный';
                        break;
                    case 2:
                        $status = 'замороженный';
                        break;
                    case 3:
                        $status = 'отключенный';
                        break;
                    case 4:
                        $status = 'удаленный';
                        break;
                    default:
                        $status = 'обычный';
                }

                $text = "<b>Информация по абоненту:</b>  \n";
                $text .= "<b>Логин:</b> " . $user['user'] . "\n";
                $text .= "<b>Пароль:</b> " . $user['password'] . "\n";
                $text .= "<b>UID:</b>" . $user['useruid'] . " \n";
                $text .= "<b>Договор:</b>" . $user['numdogovor'] . " \n";
                $text .= "<b>ФИО:</b> " . $user['fio'] . "\n";
                $text .= "<b>Тариф:</b> " . $user['tarif'] . "\n";
                $text .= "<b>Моб. телефон:</b> " . $user['mob_tel'] . "\n";
                $text .= "<b>СМС телефон:</b> " . $user['sms_tel'] . "\n";
                $text .= "<b>Баланс:</b> " . $user['deposit'] . " руб.\n";
                $text .= "<b>Кредит:</b> " . $user['credit'] . " руб.\n";
                $text .= "<b>IP:</b> " . $user['framed_ip'] . "\n";
                $text .= "<b>Интернет:</b> " . ($user['blocked'] ? '🚫' : '✅') . "\n";
                $text .= "<b>On-line:</b> " . ($user['online'] ? '✅' : '🚫') . "\n";
                $text .= "<b>Cтатус:</b> " . $status . "\n";
                $text .= "<b>Последняя авторизация:</b> " . $user['last_connection'] . "\n";
                $text .= "<b>Адрес:</b> " . $user['address'] . "\n";

                $this->sendMessage([
                    'text'         => $text,
                    'parse_mode'   => 'HTML',
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                [
                                    "text"          => "История сессий",
                                    "callback_data" => "menuHistorySessions_" . $user['useruid']
                                ],
                                [
                                    "text"          => "История платежей",
                                    "callback_data" => "menuHistoryPayments_" . $user['useruid']
                                ],
                            ],
                            [
                                [
                                    "text"          => "Услуги",
                                    "callback_data" => "menuServices_" . $user['useruid']
                                ],
                                [
                                    "text" => "Вход в ЛК",
                                    "url"  => $cabinet_host . "/index/main/lkview/login?l=" . $user['user'] . "&p=" . $user['password']
                                ],
                            ],
                            [
                                [
                                    "text"          => "🔍 Поиск",
                                    "callback_data" => "menuSearch"
                                ],
                                [
                                    'text'          => '💡 Главное меню',
                                    'callback_data' => "menuMain"
                                ]
                            ]
                        ]
                    ]
                ]);
            }

        } else {
            $text = '🤔 Абонент не найден...';

            $this->sendMessage([
                'text'         => $text,
                'parse_mode'   => 'HTML',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            [
                                "text"          => "🔍 Поиск",
                                "callback_data" => "menuSearch"
                            ],
                            [
                                'text'          => '💡 Главное меню',
                                'callback_data' => "menuMain"
                            ]
                        ]
                    ]
                ]
            ]);
        }
    }

}
