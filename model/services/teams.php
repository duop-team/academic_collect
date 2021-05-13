<?php

/**
 * Microsoft Teams communication service
 * 
 * @author Yuriy Dmitriev
 * @package Library\Teams
 **/

namespace Model\Services;
class Teams
{
  use \Library\Shared;

  private String $bearer;
  private String $tenant;
  private String $client;
  private String $scope;
  private String $secret;
  private String $baseUrl;

  private function get(String $path):?array {
    $result = null;
    $response = $this->getRequest($this->baseUrl . $path, $this->bearer);
    $result = json_decode($response, true);

    return $result;
  }

  public function getTeam(String $guid):?array {
    $result = null;
    $result = $this->get("classes/$guid");
    return $result;
  }

  public function listTeams() {
    $result = null;
    $result = $this->get("classes/");
    return $result;
  }

  public function getAssignment(String $team, String $guid):?array {
    $result = null;
    $result = $this->get("classes/$team/assignments/$guid");
    return $result;
  }

  public function listAssignments(String $team) {
    $result = null;
    $result = $this->get("classes/$team/assignment");
    return $result;
  }

  public function getSubmission(String $team, String $assignment, String $guid) {
    $result = null;
    $result = $this->get("classes/$team/assignments/$assignment/submissions/$guid");
    // TODO: grab date of submission
    return $result;
  }

  public function getPoints(String $team, String $assignment, String $submission) {
    $result = null;
    $outcome = $this->get("classes/$team/assignments/$assignment/submissions/$submission/outcomes")['value'];
    foreach ($outcome as $rubric) {
      if (isset($rubric['publishedPoints'])) {
        $result = $rubric['publishedPoints']['points'];
        break;
      }
    }
    return $result;
  }

  public function refresh():void {
    $url = "login.microsoftonline.com/". $this->tenant ."/oauth2/v2.0/token";
    $data = array(
      'client_id' => $this->client,
      'client_secret' => $this->secret,
      'scope' => $this->scope,
      'grant_type' => 'client_credentials'
    );

    $response = $this->request($url, $data);
    $result = json_decode($response, true);

    $credentials = $this->getToken();
    
		if ($credentials) {
			$this->db->update('Credentials', [
				'token' => $result['access_token'], 
			])->where(['Credentials' => ['title' => 'sync']])->run();
		} 
		else {
			$this->db->insert(['Credentials' => [
				'title' => 'sync',
				'token' => $result['access_token'],
			]])->run();
		}
  }

  private function getToken() {
    $result = null;

    $query = $this->db->select(['Credentials' => []])->where(['Credentials' => ['title' => 'sync']])->one();
    if (isset($query['token'])) {
      $result = $query['token'];
    }

    return $result;
  }

  public function __construct() {
    $this->db = $this->getDB();

    $this->tenant = $this->getVar('TENANT', 'e');
		$this->client = $this->getVar('CLIENT', 'e');
		$this->scope = 'https://graph.microsoft.com/.default';
		$this->secret = $this->getVar('SECRET', 'e');
    $this->baseUrl = 'https://graph.microsoft.com/beta/education/';
    
    $credentials = $this->getToken();

    if ($credentials) {
      $this->bearer = $credentials;
    }
    else {
      throw new Exception("Unauthorized", 3);
    }
  }
}
