<?php
class ControllerCustomZone extends Controller
{
  public function index()
  {
    $json = [];

    if (isset($this->request->get['state'])) {
      $state = $this->request->get['state'];

      // Query the database to find the zone_id by state name
      $query = $this->db->query("SELECT zone_id FROM " . DB_PREFIX . "zone WHERE name = '" . $this->db->escape($state) . "' AND country_id = '99'");

      if ($query->num_rows) {
        $json['zone_id'] = (int)$query->row['zone_id']; // cast to int for safety
      } else {
        // State provided but not found
        $json['zone_id'] = 0;
        $json['error'] = 'State not found';
      }
    } else {
      // State not provided at all
      $json['zone_id'] = 0;
      $json['error'] = 'State parameter missing';
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }
}
