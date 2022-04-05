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
            $text = "<b>Добро пожаловать,  " . $this->update->message->chat->last_name . " " . $this->update->message->chat->first_name . " ! </b> \n\n";
        } else {
            $text = "<b>Добро пожаловать ! </b> \n\n";
        }
        $text .= "Ваш ID: " . $chat_id . "\n";

        $this->sendMessage([
            'text'       => $text,
            'parse_mode' => 'HTML'
        ]);

        if ($this->isAuth()) {

            $text = "🎉 Поздравляем! Вы получили доступ к данным!";
            $this->sendMessage([
                'text'       => $text,
                'parse_mode' => 'HTML'
            ]);

            $this->menuMain();
        } else {

            $text = "🙅‍♂️ К сожалению, Вам запрещен доступ! \n";
            $text .= "Собщите ваш ID администратору и еще раз введите команду /start ";
            $this->sendMessage([
                'text'       => $text,
                'parse_mode' => 'HTML'
            ]);
        }


    }
}
