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

        if ($this->getLastAction() == 'menuSearchByLogin') {
            $this->menuSearchUser('login');
        }

        if ($this->getLastAction() == 'menuSearchByUID') {
            $this->menuSearchUser('uid');
        }

        if ($this->getLastAction() == 'menuSearchByDogovor') {
            $this->menuSearchUser('numdogovor');
        }

    }

    private function menuSearchUser($type = 'login')
    {
        $cabinet_host = config('services.mikbill.cabinet_host');

        // Ищем абона
        $api = new API();
        $users = $api->searchUsersMB($this->update->message->text, $type);
        $systemOptions = $api->getSystemOptions();

        if (!empty($users)) {

            foreach ($users as $user) {

                //Получим полную карточку абона
                $user = $api->getUserMB($user['useruid']);

                switch ($user['state']) {
                    case 1:
                        $status = $this->translate("state_1");
                        break;
                    case 2:
                        $status = $this->translate("state_2");
                        break;
                    case 3:
                        $status = $this->translate("state_3");
                        break;
                    case 4:
                        $status = $this->translate("state_4");
                        break;
                    default:
                        $status = $this->translate("state_1");
                }

                $text = "<b>" . $this->translate("user_info") . "</b>  \n";
                $text .= "<b>" . $this->translate("login") . ":</b> " . $user['user'] . "\n";
                $text .= "<b>" . $this->translate("password") . ":</b> " . $user['password'] . "\n";
                $text .= "<b>" . $this->translate("uid") . ":</b>" . $user['useruid'] . " \n";
                $text .= "<b>" . $this->translate("contract") . ":</b>" . $user['numdogovor'] . " \n";
                $text .= "<b>" . $this->translate("fio") . ":</b> " . $user['fio'] . "\n";
                $text .= "<b>" . $this->translate("tariff") . ":</b> " . $user['tarif'] . "\n";
                $text .= "<b>" . $this->translate("phone_mob") . "</b> " . $user['mob_tel'] . "\n";
                $text .= "<b>" . $this->translate("phone_sms") . ":</b> " . $user['sms_tel'] . "\n";
                $text .= "<b>" . $this->translate("deposit") . ":</b> " . $user['deposit'] . " " . (isset($systemOptions['data'][0]['UE']) ? $systemOptions['data'][0]['UE'] : 'грн.') . " \n";
                $text .= "<b>" . $this->translate("credit") . ":</b> " . $user['credit'] . " " . (isset($systemOptions['data'][0]['UE']) ? $systemOptions['data'][0]['UE'] : 'грн.') . " \n";
                $text .= "<b>Framed IP:</b> " . $user['framed_ip'] . "\n";
                $text .= "<b>Local IP:</b> " . $user['local_ip'] . "\n";
                $text .= "<b>" . $this->translate("internet") . ":</b> " . ($user['blocked'] ? '🚫' : '✅') . "\n";
                $text .= "<b>On-line:</b> " . ($user['online'] ? '✅' : '🚫') . "\n";
                $text .= "<b>" . $this->translate("status") . ":</b> " . $status . "\n";
                $text .= "<b>" . $this->translate("last_auth") . ":</b> " . $user['last_connection'] . "\n";
                $text .= "<b>" . $this->translate("address") . ":</b> " . $user['address'] . "\n";

                $this->sendMessage([
                    'text'         => $text,
                    'parse_mode'   => 'HTML',
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                [
                                    "text"          => $this->translate("menu_history_sessions"),
                                    "callback_data" => "menuHistorySessions_" . $user['useruid']
                                ],
                                [
                                    "text"          => $this->translate("menu_history_payments"),
                                    "callback_data" => "menuHistoryPayments_" . $user['useruid']
                                ],
                            ],
                            [
                                [
                                    "text"          => $this->translate("menu_services"),
                                    "callback_data" => "menuServices_" . $user['useruid']
                                ],
                                [
                                    "text" => $this->translate("cabinet_auth"),
                                    "url"  => $cabinet_host . "/index/main/lkview/login?l=" . $user['user'] . "&p=" . $user['password']
                                ],
                            ],
                            [
                                [
                                    "text"          => $this->translate("menu_search"),
                                    "callback_data" => "menuSearch"
                                ],
                                [
                                    'text'          => $this->translate("menu_main"),
                                    'callback_data' => "menuMain"
                                ]
                            ]
                        ]
                    ]
                ]);
            }

        } else {
            $text = $this->translate("user_not_found");

            $this->sendMessage([
                'text'         => $text,
                'parse_mode'   => 'HTML',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            [
                                "text"          => $this->translate("menu_search"),
                                "callback_data" => "menuSearch"
                            ],
                            [
                                'text'          => $this->translate("menu_main"),
                                'callback_data' => "menuMain"
                            ]
                        ]
                    ]
                ]
            ]);
        }
    }
}
