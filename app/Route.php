<?php
namespace app;

/**
 * Class Route
 */
class Route {

    /** Максимальное количество маршрутов с грузами */
    const ROUTES_MAX_COUNT = 50;

    /** @var  string */
    private $routes;

    /**
     * Route constructor
     * @param string $file_url
     */
    public function __construct($file_url) {
        $this->routes = $this->createRoutes($file_url);
    }

    /**
     * Метод вернёт все маршруты с грузами
     *
     * @return array
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * Метод вернёт HTML маршрутов для отображения в таблице
     *
     * @param [] $routes
     * @param [] $user_routes
     * @return string
     */
    public function getRoutesForTable($routes, $user_routes) {
        /** @var string $content */
        $content = '';

        /** @var [] $route */
        foreach ($routes as $route) {
            /** @var string $map */
            $map = ' (<a href="" class="show-map" data-routes="'.$user_routes[0].'|'.$route[1].'|'.$route[2].'|'.$user_routes[1].'">показать на карте</a>)';
            $content .= '
            <tr class="'.$route[4].'">
                <td><p>'.$route[0].'</p></td>
                <td><p>'.$route[1].' - '.$route[2].$map.'</p></td>
                <td><p>'.$route[3].' км</p></td>
            </tr>
            ';
        }

        return $content;
    }

    /**
     * Метод обраотает файл и вернёт массив маршрутов в формате
     * идентифкатор ; откуда ; куда ; расстояние
     *
     * @param string $file_url
     * @return array
     */
    private function createRoutes($file_url) {
        /** @var [] $routes */
        $routes = [];

        if (!file_exists($file_url)) {
            return $routes;
        }

        /** @var string|bool $content */
        $content = file_get_contents($file_url);
        if (!$content) {
            return $routes;
        }

        /** @var [] $data */
        $data = explode("\r\n", $content);

        foreach ($data as $route) {
            $route = explode(';', $route);
            $routes[] = array(
                $route[0], $route[1], $route[2], $route[3]
            );
        }

        return $routes;
    }

    /**
     * Метод превратит "1 234,4 км" в "1234.4"
     *
     * @param string $distance
     * @return float|int
     */
    public static function getDistance($distance) {
        $distance = str_replace(" км", "", $distance);
        $distance = str_replace(",", ".", $distance);
        $distance = preg_replace("/[^x\d|*\.]/", "", $distance);

        return $distance;
    }

    /**
     * Метод создаст случайные маршруты грузов
     *
     * @param string $file_url
     * @param string $api_key
     * @return int
     */
    public static function generateRoutes($file_url, $api_key) {
        $api_key = 'AIzaSyDKd0u_vio-Efo7hy6XecVuQbSxYBuH-ks';
        /** @var [] $routes_map */
        $routes = [];
        /** @var [] $cities */
        $cities = [
            'Москва', 'Екатеринбург', 'Челябинск', 'Уфа', 'Пермь', 'Ижевск', 'Чебоксары', 'Тюмень', 'Омск',
            'Ханты-Мансийск', 'Салехард', 'Новосибирск', 'Красноярск', 'Иркутск', 'Барнаул', 'Оренбург', 'Самара',
            'Саратов', 'Пенза', 'Волгоград', 'Сочи', 'Симферополь', 'Курск', 'Воронеж', 'Тамбов', 'Тула', 'Калуга',
            'Брянск', 'Смоленск', 'Тверь', 'Нижний Новгород', 'Великий Новгород', 'Санкт-Петербург', 'Вологда',
            'Ярославль', 'Архангельск',
        ];

        for ($i = 1; $i <= self::ROUTES_MAX_COUNT; $i++) {
            /** @var int $min */
            $min = 0;
            /** @var int $max */
            $max = count($cities) - 1;
            /** @var int $rand_from */
            $rand_from = rand($min, $max);

            do {
                $rand_to = rand($min, $max);
            } while ($rand_to == $rand_from);

            /** @var string $from */
            $from = urlencode($cities[$rand_from]);
            /** @var string $to */
            $to = urlencode($cities[$rand_to]);

            /** @var string $url */
            $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$from."&destinations=".$to."&language=ru-RU&key=".$api_key;
            /** @var Object $content */
            $content = json_decode(file_get_contents($url));

            if ($content->status == 'OK') {
                /** @var string $from */
                $from = $content->origin_addresses[0];
                /** @var string $to */
                $to = $content->destination_addresses[0];
                /** @var float|int $distance */
                $distance = self::getDistance($content->rows[0]->elements[0]->distance->text);

                $routes[] = $i.';'.$from.';'.$to.';'.$distance;
            }
        }

        return file_put_contents($file_url, implode("\r\n", $routes));
    }
}
