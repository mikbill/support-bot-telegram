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
//        $this->answerCallbackQuery([
//            'callback_query_id' => $this->update->callback_query->id,
//            'text'              => 'Загружаем...',
//            "alert"             => false
//        ]);

        $params = explode("_", $this->update->callback_query->data);

        if (isset($params[0]) and method_exists(self::class, $params[0])) {
            $method = $params[0];
            $this->$method($params); // вызываем метод
        } else {

            $this->sendMessage([
                'text'       => '⚠️ Мы еще работаем над этим меню... ',
                'parse_mode' => 'HTML'
            ]);
        }
    }

    private function menuSearch($param)
    {
        $this->setMenu('menuSearch');

        $text = '<b>Выберите поле поиска:</b>';
        $this->sendMessage([
            'text'         => $text,
            'parse_mode'   => 'HTML',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        [
                            'text'          => 'По UID',
                            'callback_data' => "menuSearchByUID"
                        ],
                        [
                            "text"          => "По логину",
                            "callback_data" => "menuSearchByLogin"
                        ],
                        [
                            'text'          => 'По договору',
                            'callback_data' => "menuSearchByDogovor"
                        ]
                    ]
                ]
            ]
        ]);
    }

    private function menuSearchByUID($param)
    {
        $this->setMenu('menuSearchByUID');

        $text = '<b>Введите UID:</b>';
        $this->sendMessage([
            'text'       => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    private function menuSearchByDogovor($param)
    {
        $this->setMenu('menuSearchByDogovor');

        $text = '<b>Введите договор:</b>';
        $this->sendMessage([
            'text'       => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    private function menuSearchByLogin($param)
    {
        $this->setMenu('menuSearchByLogin');

        $text = '<b>Введите логин:</b>';
        $this->sendMessage([
            'text'       => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    private function menuHistorySessions($param)
    {
        $this->setMenu('menuHistorySessions');

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

    private function menuServices($param)
    {
        $this->setMenu('menuServices');

        if (isset($param[1])) {
            $api = new API();
            $user = $api->getUserMB($param[1]);

            $services = $user['services'];

            $text = "<b>Услуги абонента:</b> \n\n";

            if(!empty($services['active'])){
                $text .= "Активные: \n";
                foreach ($services['active'] as $row) {
                    $text .= $row['serviceid'] . " " . $row['servicename'] . " \n";
                }
                $text.= "\n";
            }

            if(!empty($services['basic'])){
                $text .= "Базовые: \n";
                foreach ($services['basic'] as $row) {
                    $text .= $row['serviceid'] . " " . $row['servicename'] . " \n";
                }
                $text.= "\n";
            }

            if(!empty($services['personal'])){
                $text .= "Индивидуальные: \n";
                foreach ($services['personal'] as $row) {
                    $text .= $row['serviceid'] . " " . $row['servicename'] . " \n";
                }
                $text.= "\n";
            }

            if(empty($services['active']) and empty($services['basic']) and empty($services['personal'])){
                $text .= "🤔 Услуги не найдены...";
            }

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

    private function menuHistoryPayments($param)
    {
        $this->setMenu('menuHistoryPayments');

        if (isset($param[1])) {
            $api = new API();
            $history = $api->getHistoryPaymentsMB($param[1]);

            $text = "История платежей: \n\n";
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

    private function menuCabinet()
    {

    }

    private function menuHelp()
    {
        $text = "🧑‍💻 Мы работаем,еще чуть чуть и здесь будет справка.";

        $this->sendMessage([
            'text'       => $text,
            'parse_mode' => 'HTML'
        ]);
    }
}
