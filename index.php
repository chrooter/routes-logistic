<?php
header('Content-Type: text/html; charset=utf-8');

include_once('app/Route.php');
include_once('app/User.php');

/** @var [] $config */
$config = require('config.php');
if (file_exists('config-dev.php')) {
	$config = array_merge(
			require('config.php'),
			require('config-dev.php')
	);
}

use app\User;
use app\Route;

/** @var string $file база грузов с дистанцией (будем брать из БД) */
$file = "routes.txt";

if (isset($_POST['new-request'])) {
	if (Route::generateRoutes($file, $config['google-api-key']) != false) {
		header('Location: '.$_SERVER['PHP_SELF']);
	}
}

/** @var Route $route */
$route = new Route($file);
/** @var [] $routes */
$routes = $route->getRoutes();

/** @var string $route_1_active */
$route_1_active = '';
/** @var string $route_2_active */
$route_2_active = '';
/** @var null|int $find_count_1 */
$find_count_1 = null;
/** @var null|int $find_count_2 */
$find_count_2 = null;
/** @var int $route_1_distance */
$route_1_distance = 30;
/** @var int $route_2_distance */
$route_2_distance = 30;
/** @var [] $route_for_js */
$route_for_js = [];

if (isset($_POST['route-1-distance'])) {
	$route_1_active = 'bg-info';
	$route_for_js = [User::USER_POINT_A, User::USER_POINT_B];

	/** @var int $route_1_distance */
	$route_1_distance = (int) $_POST['route-1-distance'];
	/** @var User $user */
	$user = new User($route_1_distance, User::USER_POINT_A, User::USER_POINT_B);

	list ($routes, $find_count_1) = $user->findRoutes($routes, $config['google-api-key']);
}

if (isset($_POST['route-2-distance'])) {
	$route_2_active = 'bg-info';
	$route_for_js = [User::USER_POINT_B, User::USER_POINT_A];

	/** @var int $route_2_distance */
	$route_2_distance = (int) $_POST['route-2-distance'];
	/** @var User $user */
	$user = new User($route_2_distance, User::USER_POINT_B, User::USER_POINT_A);

	list ($routes, $find_count_2) = $user->findRoutes($routes, $config['google-api-key']);
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Route Logistic</title>
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
		</head>
		<body>
			<div class="row">
				<div class="col-md-6">
					<h3>Мои маршруты</h3>
					
					<div class="<?= $route_1_active ?>" style="padding: 10px 0;">
						<h5>1. <?= User::USER_POINT_A ?> - <?= User::USER_POINT_B ?> (<strong><?= User::USER_ROUTE_KM ?></strong> км)</h5>
						<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" class="form-inline">
							<div class="form-group">
								<label for="route-1-distance">Не дальше чем (километров):</label>
								<input type="text" class="form-control" name="route-1-distance" id="route-1-distance" value="<?= $route_1_distance ?>" size="5">
							</div>
							<button type="submit" class="btn btn-default">Найти запросы</button>
						</form>
						<?php if (!is_null($find_count_1)) { ?><h5>Найдено маршрутов: <strong><?= $find_count_1 ?></strong></h5><?php } ?>
					</div>
					
					<div class="<?= $route_2_active ?>" style="padding: 10px 0;">
						<h5>2. <?= User::USER_POINT_B ?> - <?= User::USER_POINT_A ?> (<strong><?= User::USER_ROUTE_KM ?></strong> км)</h5>
						<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" class="form-inline">
							<div class="form-group">
								<label for="route-2-distance">Не дальше чем (километров):</label>
								<input type="text" class="form-control" name="route-2-distance" id="route-2-distance" value="<?= $route_2_distance ?>" size="5">
							</div>
							<button type="submit" class="btn btn-default">Найти запросы</button>
						</form>
						<?php if (!is_null($find_count_2)) { ?><h5>Найдено маршрутов: <strong><?= $find_count_2 ?></strong></h5><?php } ?>
					</div>
				</div>
				
				<div class="col-md-6">
					<h3>Создание запросов</h3>
					<p>Можно создать 50 случайных запросов на перевозку грузов</p>
					<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
						<button type="submit" name="new-request" class="btn btn-default">Создать новые запросы</button>
					</form>
				</div>
			</div>
			
			<br /><div class="clearfix"></div><br />
			
			<table class="table table-striped">
				<thead>
					<th>#</th>
					<th>Маршрут</th>
					<th>Дистанция</th>
				</thead>
				<tbody>
					<?php
					if ($routes) {
						echo $route->getRoutesForTable($routes, $route_for_js);
					}
					?>
				</tbody>
			</table>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
		<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/scripts.js"></script>
	</body>
</html>