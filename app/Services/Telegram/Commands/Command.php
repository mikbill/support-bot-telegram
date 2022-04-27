<?php


namespace App\Services\Telegram\Commands;


use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use WeStacks\TeleBot\Handlers\CommandHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

abstract class Command extends CommandHandler
{
    private $isAuth = false;
    private $user_id = -1;

    public function __construct(TeleBot $bot, Update $update)
    {
        parent::__construct($bot, $update);

        $locale = $this->getLocale();

        dump($locale);

        //  Переключим язык пользователя
        App::setLocale($locale);

        $this->checkAuth();
    }

    public function isAuth()
    {
        return $this->isAuth;
    }

    public function checkAuth()
    {
        $allowed_id = config('telebot.bots.bot.allowed_id');

        if (isset($this->update->message->from->id)) {
            $this->user_id = $this->update->message->from->id;
        } elseif (isset($this->update->callback_query->from->id)) {
            $this->user_id = $this->update->callback_query->from->id;
        }

        if(empty($allowed_id)) {
            return $this->isAuth = true;
        }

        return $this->isAuth = in_array($this->user_id, $allowed_id);
    }

    public function setLastAction($action)
    {
        Cache::put($this->user_id . '_last_action', $action);
    }

    public function getLastAction()
    {
        return Cache::get($this->user_id . '_last_action');
    }

    public function setLocale($locale) {
        Cache::put($this->user_id . '_locale', $locale);
        // Установим язык
        App::setLocale($locale);

    }

    public function getLocale() {
        $locale = Cache::get($this->user_id . '_locale');

        if( empty($locale) ) {
            $locale = app()->getLocale();
        }

        return $locale;
    }

    public function translate($message, $replace = [], $locale = null) {

        if( $locale == null ) {
            $locale = $this->getLocale();
        }

        return trans($message, $replace, $locale);
    }
}
