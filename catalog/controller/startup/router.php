<?php
class ControllerStartupRouter extends Controller
{
	public function index()
	{
		// Route
		if (isset($this->request->get['route']) && $this->request->get['route'] != 'startup/router') {
			$route = $this->request->get['route'];
		} else {
			$route = $this->config->get('action_default');
		}

		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);


		// Trigger the pre events
		$result = $this->event->trigger('controller/' . $route . '/before', array(&$route, &$data));

		if (!is_null($result)) {
			return $result;
		}

		// We dont want to use the loader class as it would make an controller callable.
		$action = new Action($route);

		// Any output needs to be another Action object.
		$output = $action->execute($this->registry);

		// Trigger the post events
		$result = $this->event->trigger('controller/' . $route . '/after', array(&$route, &$data, &$output));

		if (!is_null($result)) {
			return $result;
		}

		// $route['vendor/order'] = 'vendor/order/index';
		// $route['vendor/shipway/generateLabel'] = 'vendor/shipway/generateLabel';

		if (isset($this->request->get['route']) && $this->request->get['route'] == 'vendor/getVendorData') {
			return new Action('vendor/edit/getVendorData');
		}

		// if (isset($this->request->get['route']) && $this->request->get['route'] == 'vendor/createManifest') {
		// 	$action = new Action('vendor/createmanifest');
		// }

		if (isset($this->request->get['route']) && $this->request->get['route'] == 'vendor/order_report/createManifest') {
			$action = new Action('vendor/order_report/createManifest');
		}


		// if ($route == "vendor/commission/getCourierRates") {
		// 	return new Action("vendor/commission/getCourierRates");
		// }

		if ($route == "vendor/order_report/getCourierRates") {
			return new Action("vendor/order_report/getCourierRates");
		}


		
		
		
		
		
		




		return $output;
	}
}
