ymaps.ready(init);

function init () {
	$('.show-map').click(function () {
		var routeMap;
		$('#map').remove();
		$(this).closest('td').append('<div id="map" style="width: 800px; height: 400px; padding: 0; margin: 0;"></div>');

		var routes = $(this).attr('data-routes').split('|');

		var multiRoute = new ymaps.multiRouter.MultiRoute({
			referencePoints: routes
		});

		var removePointsButton = new ymaps.control.Button({
			data:    {content: "Удалить промежуточные точки"},
			options: {selectOnClick: true}
		});

		removePointsButton.events.add('select', function () {
			multiRoute.model.setReferencePoints([
				routes[0],
				routes[3]
			], []);
		});

		removePointsButton.events.add('deselect', function () {
			multiRoute.model.setReferencePoints(routes);
		});

		var myGeocoder = ymaps.geocode(routes[2]);
		myGeocoder.then(
			function (res) {
				var center = res.geoObjects.get(0).geometry.getCoordinates();

				var routeMap = new ymaps.Map('map', {
					center:   center,
					zoom:     8,
					controls: [removePointsButton]
				}, {
					buttonMaxWidth: 300
				});

				routeMap.geoObjects.add(multiRoute);
			},
			function (err) {
				// обработка ошибки
			}
		);

		return false;
	});
}