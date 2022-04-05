<?php


namespace App\Services\MikBill\Admin;


use Kagatan\MikBillAdminAPI\AdminAPI;

class API extends AdminAPI
{
    private $host = 'https://admin2x.loc';
    private $login = 'admin';
    private $pass = 'admin';

    public function __construct()
    {
        $this->host = config('services.mikbill.host');
        $this->login = config('services.mikbill.login');
        $this->pass = config('services.mikbill.pass');

        parent::__construct($this->login, $this->pass, $this->host);
    }

    /**
     * Поиск абонента в админке биллинга
     *
     * @param $value
     * @param string $type
     * @return array|bool
     */
    public function searchUsersMB($value, $type = 'login')
    {
        switch ($type) {
            case 'uid':
                return $this->searchByField('uid', 'uid', $value);
                break;
            case 'login':
                return $this->searchByField('user', 'user', $value);
                break;
            case 'numdogovor':
                return $this->searchByField('uid', 'numdogovor', $value);
                break;
        }

        return false;
    }


    private function searchByField($field, $key, $value)
    {
        $params = [
            $field                   => $value,
            "search_normal_state"    => 0,
            "search_otkluchen_state" => 0,
            "search_frozen_state"    => 0,
            "search_deleted_state"   => 0,
            "search_all_states"      => 1,
            "op"                     => 1,
            "search_display_all"     => 0,
            "search_internet"        => 0,
            "ext_legal_person"       => 0,
            "ext_regular_person"     => 0
        ];

        $res = $this->getUsers($params);

        if (isset($res['success'], $res['data']) and $res['success'] == true and is_array($res['data'])) {
            $users = [];
            foreach ($res['data'] as $user) {
                if ($user[$key] == $value) {
                    $users[] = $user;
                }
            }

            return $users;
        }

        return [];
    }

    public function getUserMB($uid)
    {
        $params = [
            'uid' => $uid
        ];
        $res = $this->getUser($params);

        if (isset($res['success'], $res['data']) and $res['success'] == true) {
            return $res['data'];
        }

        return false;
    }

    public function getHistorySessionsMB($uid)
    {
        $params = [
            'uid' => $uid
        ];
        $res = $this->getUserCanvasStat($params);

        if (isset($res['data'][0]['stattraf'])) {
            return $res['data'][0]['stattraf'];
        }

        return [];
    }

    public function getHistoryPaymentsMB($uid)
    {
        $params = [
            'uid' => $uid
        ];
        $res = $this->getUserCanvasStat($params);

        if (isset($res['data'][0]['statpay'])) {
            return $res['data'][0]['statpay'];
        }

        return false;
    }

}
