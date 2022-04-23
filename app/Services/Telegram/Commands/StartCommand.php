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
            $text = "<b>" . trans("hello") . ",  " . $this->update->message->chat->last_name . " " . $this->update->message->chat->first_name . " ! </b> 👋 \n\n";
        } else {
            $text = "<b>" . trans("hello") . "! </b> 👋 \n\n";
        }
        $text .= trans("your_id") . " " . $chat_id . "\n";

        $this->sendMessage([
            'text'       => $text,
            'parse_mode' => 'HTML'
        ]);

        if ($this->isAuth()) {

            $text = trans("auth_success");
            $this->sendMessage([
                'text'       => $text,
                'parse_mode' => 'HTML'
            ]);

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

        } else {

            $text = trans("auth_error");
            $this->sendMessage([
                'text'       => $text,
                'parse_mode' => 'HTML'
            ]);
        }
    }
}
