<?php


namespace App\Services\Telegram\Commands;


use App\Services\MikBill\Admin\API;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class CallBackCommand extends Command
{

    /**
     * Обрабатываем callback_query
     *
     * @param Update $update
     * @param TeleBot $bot
     * @return bool
     */
    public static function trigger(Update $update, TeleBot $bot)
    {
        return isset($update->callback_query);
    }

    public function handle()
    {
        $params = explode("_", $this->update->callback_query->data);

        if (isset($params[0]) and method_exists(self::class, $params[0])) {
            $method = $params[0];
            $this->$method($params); // вызываем метод
        } else {

            $this->sendMessage([
                'text'       => trans("menu_not_work"),
                'parse_mode' => 'HTML'
            ]);
        }
    }

    private function menuSearch($param)
    {
        $this->setLastAction('menuSearch');

        $text = "<b>" . trans("choice_search_field") . "</b>";

        $this->sendMessage([
            'text'         => $text,
            'parse_mode'   => 'HTML',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        [
                            'text'          => trans("menu_search_by_uid"),
                            'callback_data' => "menuSearchByUID"
                        ],
                        [
                            "text"          => trans("menu_search_by_login"),
                            "callback_data" => "menuSearchByLogin"
                        ],
                        [
                            'text'          => trans("menu_search_by_contract"),
                            'callback_data' => "menuSearchByDogovor"
                        ]
                    ]
                ]
            ]
        ]);
    }

    private function menuSearchByUID($param)
    {
        $this->setLastAction('menuSearchByUID');

        $text = "<b>" . trans("enter_uid") . "</b>";
        $this->sendMessage([
            'text'       => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    private function menuSearchByDogovor($param)
    {
        $this->setLastAction('menuSearchByDogovor');

        $text = "<b>" . trans("enter_contract") . "</b>";
        $this->sendMessage([
            'text'       => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    private function menuSearchByLogin($param)
    {
        $this->setLastAction('menuSearchByLogin');

        $text = "<b>" . trans("enter_login") . "</b>";
        $this->sendMessage([
            'text'       => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    private function menuHistorySessions($param)
    {
        $this->setLastAction('menuHistorySessions');

        if (isset($param[1])) {
            $api = new API();
            $history = $api->getHistorySessionsMB($param[1]);

            $text = "История ceccий: \n\n";
            $text .= "<pre> " . str_pad('Start time', 20) . " | " . str_pad('Stop time', 20) . " | " . str_pad('Time on', 15) . "</pre>\n";
            $text .= "<pre> " . str_pad('-', 20, '-') . " + " . str_pad('-', 20, '-') . " + " . str_pad('-', 15, '-') . "</pre>\n";
            foreach ($history as $row) {
                $text .= "<pre> " . str_pad($row['start_time'], 20) . " | " . str_pad($row['stop_time'], 20) . " | " . str_pad($row['time_on'], 15) . "</pre>\n";
            }

            $this->sendMessage([
                'text'         => $text,
                'parse_mode'   => 'HTML',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            [
                                "text"          => trans("menu_search"),
                                "callback_data" => "menuSearch"
                            ],
                            [
                                'text'          => trans("menu_main"),
                                'callback_data' => "menuMain"
                            ]
                        ]
                    ]
                ]
            ]);
        }

    }

    private function menuServices($param)
    {
        $this->setLastAction('menuServices');

        if (isset($param[1])) {
            $api = new API();
            $user = $api->getUserMB($param[1]);

            $services = $user['services'];

            $text = "<b>" . trans("services_user") . "</b> \n\n";

            if (!empty($services['active'])) {
                $text .= trans("services_active") . " \n";
                foreach ($services['active'] as $row) {
                    $text .= $row['serviceid'] . " " . $row['servicename'] . " \n";
                }
                $text .= "\n";
            }

            if (!empty($services['basic'])) {
                $text .= trans("services_basic") . " \n";
                foreach ($services['basic'] as $row) {
                    $text .= $row['serviceid'] . " " . $row['servicename'] . " \n";
                }
                $text .= "\n";
            }

            if (!empty($services['personal'])) {
                $text .= trans("services_individual") . " \n";
                foreach ($services['personal'] as $row) {
                    $text .= $row['serviceid'] . " " . $row['servicename'] . " \n";
                }
                $text .= "\n";
            }

            if (empty($services['active']) and empty($services['basic']) and empty($services['personal'])) {
                $text .= trans("services_not_found");
            }

            $this->sendMessage([
                'text'         => $text,
                'parse_mode'   => 'HTML',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            [
                                "text"          => trans("menu_search"),
                                "callback_data" => "menuSearch"
                            ],
                            [
                                'text'          => trans("menu_main"),
                                'callback_data' => "menuMain"
                            ]
                        ]
                    ]
                ]
            ]);
        }
    }

    private function menuHistoryPayments($param)
    {
        $this->setLastAction('menuHistoryPayments');

        if (isset($param[1])) {
            $api = new API();
            $history = $api->getHistoryPaymentsMB($param[1]);

            $text = trans("history_payment") . " \n\n";
            $text .= "<pre> " . str_pad('Date', 20) . " | " . str_pad('Summa', 10) . " | " . str_pad('Type', 40) . " </pre>\n";
            $text .= "<pre> " . str_pad('-', 20, '-') . " + " . str_pad('-', 10, '-') . " + " . str_pad('-', 40, '-') . " </pre>\n";
            foreach ($history as $row) {
                $text .= "<pre> " . str_pad($row['date'], 20) . " | " . str_pad($row['summa'], 10) . " | " . str_pad($row['bughtypeid'], 40) . " </pre>\n";
            }

            $this->sendMessage([
                'text'         => $text,
                'parse_mode'   => 'HTML',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            [
                                "text"          => trans("menu_search"),
                                "callback_data" => "menuSearch"
                            ],
                            [
                                'text'          => trans("menu_main"),
                                'callback_data' => "menuMain"
                            ]
                        ]
                    ]
                ]
            ]);
        }

    }

    private function menuMain()
    {
        $this->setLastAction('menuMain');

        $this->sendMessage([
            'text'         => "<b>" . trans("main_menu") . "</b>",
            'parse_mode'   => 'HTML',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        [
                            "text"          => trans("menu_search"),
                            "callback_data" => "menuSearch"
                        ]
                    ]
                ]
            ]
        ]);
    }

    private function menuHelp()
    {
        $this->setLastAction('menuHelp');

        $text = trans("menu_not_work");

        $this->sendMessage([
            'text'       => $text,
            'parse_mode' => 'HTML'
        ]);
    }
}
