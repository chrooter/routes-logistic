<?php
namespace app;

class User {

    /** Расстояние маршрутов пользователя */
    const USER_ROUTE_KM = 2604;

    const USER_POINT_A = 'Москва';
    const USER_POINT_B = 'Ханты-Мансийск';

    /** @var  string */
    private $start_point;
    /** @var  string */
    private $finish_point;

    /** @var int дополнительное расстояние, которое готов проехать пользователь */
    private $additional_distance = 30;

    /**
     * User constructor.
     * @param int $additional_distance
     * @param string $start_point
     * @param string $finish_point
     */
    public function __construct($additional_distance, $start_point, $finish_point) {
        $this->additional_distance = $additional_distance;
        $this->start_point = urlencode($start_point);
        $this->finish_point = urlencode($finish_point);
    }

    /**
     * @return int
     */
    private function getMaxDistance() {
        return $this->additional_distance + self::USER_ROUTE_KM;
    }

     /**
     * @param [] $routes
      * @param string $api_key
     * @return array
     */
    public function findRoutes($routes, $api_key) {
        $api_key = 'AIzaSyDKd0u_vio-Efo7hy6XecVuQbSxYBuH-ks';
        /** @var int $success_route */
        $success_route = 0;

        foreach ($routes as &$route) {
            $route_start = urlencode($route[1]);
            $route_finish = urlencode($route[2]);

            $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$this->start_point."|".$route_finish."&destinations=".$route_start."|".$this->finish_point."&language=ru-RU&key=".$api_key;
            /** @var Object $content */
            $content = json_decode(file_get_contents($url));

            if ($content->status == 'OK') {
                /** @var float $distance_p1 расстояние от точки старта пользователя до точки получения груза */
                $distance_p1 = Route::getDistance($content->rows[0]->elements[0]->distance->text);
                /** @var float $distance_p2 расстояние от точки выдачи груза до точки финиша пользователя */
                $distance_p2 = Route::getDistance($content->rows[1]->elements[1]->distance->text);
                /** @var float $distance дистанция всего маршрута */
                $distance = $distance_p1 + $route[3] + $distance_p2;

                // Если суммарная дистанция больше расстояния, которое готов проехать пользователь,
                // то такой маршрут не подходит
                if ($distance <= $this->getMaxDistance()) {
                    $route[4] = 'success';
                    $success_route++;
                }
            }

            unset($route);
        }

        return [$routes, $success_route];
    }
}
