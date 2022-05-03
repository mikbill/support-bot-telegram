<?php


namespace App\Services\Telegram\Commands;

use App;
use App\Helpers\Helpers;
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
                        ]
                    ],
                    [
                        [
                            'text'          => trans("menu_search_by_contract"),
                            'callback_data' => "menuSearchByDogovor"
                        ],
                        [
                            'text'          => trans("menu_search_by_phone"),
                            'callback_data' => "menuSearchByPhone"
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

    private function menuSearchByPhone($param)
    {
        $this->setLastAction('menuSearchByPhone');

        $text = "<b>" . trans("enter_phone") . "</b>";
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
            $result = $api->getUserShortHistory([
                "uid" => $param[1]
            ]);

            $history = isset($result['data'][0]['stattraf']) ? $result['data'][0]['stattraf'] : [];

            $text = "История ceccий: \n\n";
            $text .= "<pre> " . Helpers::str_pad_unicode('Start time', 20) . " | " . Helpers::str_pad_unicode('Stop time', 20) . " | " . Helpers::str_pad_unicode('Time on', 15) . "</pre>\n";
            $text .= "<pre> " . Helpers::str_pad_unicode('-', 20, '-') . " + " . Helpers::str_pad_unicode('-', 20, '-') . " + " . Helpers::str_pad_unicode('-', 15, '-') . "</pre>\n";
            foreach ($history as $row) {
                $text .= "<pre> " . Helpers::str_pad_unicode($row['start_time'], 20) . " | " . Helpers::str_pad_unicode($row['stop_time'], 20) . " | " . Helpers::str_pad_unicode($row['time_on'], 15) . "</pre>\n";
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
            $result = $api->getUserShortHistory([
                "uid" => $param[1]
            ]);

            $history = isset($result['data'][0]['statpay']) ? $result['data'][0]['statpay'] : [];

            $text = trans("history_payment") . " \n\n";
            $text .= "<pre> " . Helpers::str_pad_unicode('Date', 20) . " | " . Helpers::str_pad_unicode('Summa', 10) . " | " . Helpers::str_pad_unicode('Type', 40) . " </pre>\n";
            $text .= "<pre> " . Helpers::str_pad_unicode('-', 20, '-') . " + " . Helpers::str_pad_unicode('-', 10, '-') . " + " . Helpers::str_pad_unicode('-', 40, '-') . " </pre>\n";
            foreach ($history as $row) {
                $text .= "<pre> " . Helpers::str_pad_unicode($row['date'], 20) . " | " . Helpers::str_pad_unicode($row['summa'], 10) . " | " . Helpers::str_pad_unicode($row['bughtypeid'], 40) . " </pre>\n";
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

    private function menuHistoryTickets($param)
    {
        $this->setLastAction('menuHistoryTickets');

        if (isset($param[1])) {
            $api = new API();
            $result = $api->getUserShortHistory([
                "uid" => $param[1]
            ]);

            $history = isset($result['data'][0]['tickets']) ? $result['data'][0]['tickets'] : [];

            $text = trans("history_tickets") . " \n\n";
            $text .= "<pre> " . Helpers::str_pad_unicode('Date create', 20) . " | " . Helpers::str_pad_unicode('Category', 25) . " | " . Helpers::str_pad_unicode('Status', 40) . " </pre>\n";
            $text .= "<pre> " . Helpers::str_pad_unicode('-', 20, '-') . " + " . Helpers::str_pad_unicode('-', 25, '-') . " + " . Helpers::str_pad_unicode('-', 40, '-') . " </pre>\n";
            foreach ($history as $row) {
                $text .= "<pre> " . Helpers::str_pad_unicode($row['creationdate'], 20) . " | " . Helpers::str_pad_unicode($row['categoryname'], 25) . " | " . Helpers::str_pad_unicode($row['statustypename'], 40) . " </pre>\n";
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

    private function menuHistoryAuths($param)
    {
        $this->setLastAction('menuHistoryAuths');

        if (isset($param[1])) {

            $api = new API();
            $result = $api->getUserShortHistory([
                "uid" => $param[1]
            ]);

            $history = isset($result['data'][0]['postauth']) ? $result['data'][0]['postauth'] : [];

            $text = trans("history_auths") . " \n\n";
            $text .= "<pre> " . Helpers::str_pad_unicode('Date auth', 20) . " | " . Helpers::str_pad_unicode('Calling Station Id', 20) . " | " . Helpers::str_pad_unicode('Message', 40) . " </pre>\n";
            $text .= "<pre> " . Helpers::str_pad_unicode('-', 20, '-') . " + " . Helpers::str_pad_unicode('-', 20, '-') . " + " . Helpers::str_pad_unicode('-', 40, '-') . " </pre>\n";
            foreach ($history as $row) {
                $text .= "<pre> " . Helpers::str_pad_unicode($row['authdate'], 20) . " | " . Helpers::str_pad_unicode($row['callingstationid'], 20) . " | " . Helpers::str_pad_unicode($row['replymessage'], 40) . " </pre>\n";
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

    private function menuHistoryLogs($param)
    {
        $this->setLastAction('menuHistoryLogs');

        if (isset($param[1])) {

            $api = new API();
            $result = $api->getUserShortHistory([
                "uid" => $param[1]
            ]);

            $history = isset($result['data'][0]['logs']) ? $result['data'][0]['logs'] : [];

            $text = trans("history_logs") . " \n\n";
            $text .= "<pre> " . Helpers::str_pad_unicode('Date', 20) . " | " . Helpers::str_pad_unicode('Value', 40) . " | " . Helpers::str_pad_unicode('Old', 20) . " | " . Helpers::str_pad_unicode('New', 20) . " </pre>\n";
            $text .= "<pre> " . Helpers::str_pad_unicode('-', 20, '-') . " + " . Helpers::str_pad_unicode('-', 40, '-') . " + " . Helpers::str_pad_unicode('-', 20, '-') . " + " . Helpers::str_pad_unicode('-', 20, '-') . " </pre>\n";
            foreach ($history as $row) {
                $text .= "<pre> " . Helpers::str_pad_unicode($row['date'], 20) . " | " . Helpers::str_pad_unicode($row['valuename'], 40) . " | " . Helpers::str_pad_unicode($row['oldvalue'], 20) . " | " . Helpers::str_pad_unicode($row['newvalue'], 20) . " </pre>\n";
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


    private function menuUserKick($param)
    {
        $this->setLastAction('menuUserKick');

        if (isset($param[1])) {
            $api = new API();
            $result = $api->userKickOnline([
                "uid" => $param[1]
            ]);

            $text = trans("command_sended") . " \n\n";

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
                        ],
                        [
                            "text"          => trans("menu_locale"),
                            "callback_data" => "menuLocale"
                        ]
                    ]
                ]
            ]
        ]);
    }

    private function menuLocale()
    {
        $this->setLastAction('menuLocale');

        $this->sendMessage([
            'text'         => "<b>" . trans("menu_locale") . "</b>",
            'parse_mode'   => 'HTML',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        [
                            "text"          => trans("menu_locale_ua"),
                            "callback_data" => "menuSeTLocaleUa"
                        ],
                        [
                            "text"          => trans("menu_locale_ru"),
                            "callback_data" => "menuSeTLocaleRu"
                        ],
                        [
                            "text"          => trans("menu_locale_en"),
                            "callback_data" => "menuSeTLocaleEn"
                        ]
                    ]
                ]
            ]
        ]);
    }

    private function menuSeTLocaleUa()
    {
        $this->setLocale("uk");
        $this->menuMain();
    }

    private function menuSeTLocaleRu()
    {
        $this->setLocale("ru");
        $this->menuMain();
    }

    private function menuSeTLocaleEn()
    {
        $this->setLocale("en");
        $this->menuMain();
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
