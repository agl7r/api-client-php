<?php

use PHPUnit\Framework\TestCase;
use UchiPro\ApiClient;
use UchiPro\Identity;
use UchiPro\Orders\Query;

class OrdersTest extends TestCase
{
    private $config;

    public function setUp()
    {
        $this->config = require 'config.php';
    }

    private function getAdministratorIdentity()
    {
        $url = $this->config['url'];

        foreach ($this->config['users'] as $user) {
            if ($user['role'] === 'administrator') {
                if (!empty($user['token'])) {
                    return Identity::createByAccessToken($url, $user['token']);
                } elseif (!empty($user['login']) && !empty($user['password'])) {
                    return Identity::createByLogin($url, $user['login'], $user['password']);
                }
            }
        }

        return null;
    }

    /**
     * @return ApiClient
     */
    public function getApiClient()
    {
        return ApiClient::create($this->getAdministratorIdentity());
    }

    public function testGetOrders()
    {
        $query = new Query();
        $query->number = '28486/2019-2';
        $orders = $this->getApiClient()->orders()->findBy($query);

        $this->assertTrue(is_array($orders));
    }
}
