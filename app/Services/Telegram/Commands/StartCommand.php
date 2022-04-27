<?php


namespace App\Services\Telegram\Commands;


use WeStacks\TeleBot\Handlers\CommandHandler;

class StartCommand extends Command
{
    protected static $aliases = ['/start', '/s'];
    protected static $description = 'Send "/start" or "/s" to get "Hello, World!"';

    public function handle()
    {
        $chat_id = $this->update->message->from->id;

        if (isset($this->update->message->chat->last_name, $this->update->message->chat->first_name)) {
            $text = "<b>" . $this->translate("hello") . ",  " . $this->update->message->chat->last_name . " " . $this->update->message->chat->first_name . " ! </b> 👋 \n\n";
        } else {
            $text = "<b>" . $this->translate("hello") . "! </b> 👋 \n\n";
        }
        $text .= $this->translate("your_id") . " " . $chat_id . "\n";

        $this->sendMessage([
            'text'       => $text,
            'parse_mode' => 'HTML'
        ]);

        if ($this->isAuth()) {

            $text = $this->translate("auth_success");
            $this->sendMessage([
                'text'       => $text,
                'parse_mode' => 'HTML'
            ]);

            $this->setLastAction('menuMain');

            $this->sendMessage([
                'text'         => "<b>" . $this->translate("main_menu") . "</b>",
                'parse_mode'   => 'HTML',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            [
                                "text"          => $this->translate("menu_search"),
                                "callback_data" => "menuSearch"
                            ],
                            [
                                "text"          => $this->translate("menu_locale"),
                                "callback_data" => "menuLocale"
                            ]
                        ]
                    ]
                ]
            ]);

        } else {

            $text = $this->translate("auth_error");
            $this->sendMessage([
                'text'       => $text,
                'parse_mode' => 'HTML'
            ]);
        }
    }
}
