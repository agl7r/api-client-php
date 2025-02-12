<?php

/**
 * Пример сбора статистики по самым популярным курсам в системе.
 */

use UchiPro\{ApiClient, Courses\Course, Identity, Orders\Order, Orders\Query};

require '../vendor/autoload.php';

$orders = fetchCompletedOrders();
$statistics = collectStatistics($orders);
showPopularCourses($statistics);

/**
 * @param array|\StatisticsItem[] $statistics
 */
function showPopularCourses(array $statistics)
{
    $limit = 10;

    usort($statistics, function (StatisticsItem $a, StatisticsItem $b) {
        return $a->orders < $b->orders;
    });

    print 'Популярные курсы по числу заявок: '.PHP_EOL;
    $i = 0;
    foreach ($statistics as $statisticsItem) {
        $i++;
        print "{$i}. {$statisticsItem->course->title}: {$statisticsItem->orders}".PHP_EOL;
        if ($i === $limit) {
            break;
        }
    }

    usort($statistics, function (StatisticsItem $a, StatisticsItem $b) {
        return $a->listeners < $b->listeners;
    });

    print PHP_EOL;

    print 'Популярные курсы по числу слушателей: '.PHP_EOL;
    $i = 0;
    foreach ($statistics as $statisticsItem) {
        $i++;
        print "{$i}. {$statisticsItem->course->title}: {$statisticsItem->listeners}".PHP_EOL;
        if ($i === $limit) {
            break;
        }
    }
}

/**
 * @param array|Order[] $orders
 *
 * @return array|StatisticsItem[]
 */
function collectStatistics(array $orders)
{
    $statistics = [];

    foreach ($orders as $order) {
        if (!isset($statistics[$order->course->id])) {
            $statistics[$order->course->id] = new StatisticsItem();
            $statistics[$order->course->id]->course = $order->course;
        }

        $statistics[$order->course->id]->orders += 1;
        $statistics[$order->course->id]->listeners += $order->listenersCount;
    }

    return $statistics;
}

class StatisticsItem
{
    /**
     * @var Course
     */
    public $course;

    /**
     * @var int
     */
    public $orders = 0;

    /**
     * @var int
     */
    public $listeners = 0;
}

/**
 * @return ApiClient
 */
function getApiClient()
{
    $url = getenv('UCHIPRO_URL');
    $login = getenv('UCHIPRO_LOGIN');
    $password = getenv('UCHIPRO_PASSWORD');

    $identity = Identity::createByLogin($url, $login, $password);
    $apiClient = ApiClient::create($identity);

    return $apiClient;
}

/**
 * @return array|Order[]
 */
function fetchCompletedOrders()
{
    $orders = [];

    $apiClient = getApiClient();

    $query = new Query();
    $query->status = $query::STATUS_COMPLETED;
    foreach ($apiClient->orders()->findBy() as $order) {
        if ($order->listenersCount === $order->listenersFinished) {
            $orders[] = $order;
        }
    }

    return $orders;
}
