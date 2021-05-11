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

  public function team(String $guid):?array
  {
    $result = null;
    $result = $this->get("classes/$guid");
    return $result;
  }

  public function assignment(String $team, String $guid):?array
  {
    $result = null;
    $result = $this->get("classes/$team/assignments/$guid");
    return $result;
  }

  public function submission(String $team, String $assignment, String $guid)
  {
    $result = null;
    $result = $this->get("classes/$team/assignments/$assignment/submissions/$guid");
    // TODO: grab date of submission
    return $result;
  }

  public function points(String $team, String $assignment, String $submission)
  {
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

  public function refresh():void
  {
    $url = "login.microsoftonline.com/". $this->tenant ."/oauth2/v2.0/token";
    $data = array(
      'client_id' => $this->client,
      'client_secret' => $this->secret,
      'scope' => $this->scope,
      'grant_type' => 'client_credentials'
    );

    $response = $this->request($url, $data);
    $result = json_decode($response, true);

    $credentials = $this->db->select(['Credentials' => []])
									->where(['Credentials' => ['title' => 'sync']])->one();
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
  public function __construct() {
    $this->db = $this->getDB();

    $this->tenant = $this->getVar('TENANT', 'e');
		$this->client = $this->getVar('CLIENT', 'e');
		$this->scope = 'https://graph.microsoft.com/.default';
		$this->secret = $this->getVar('SECRET', 'e');
    $this->baseUrl = 'https://graph.microsoft.com/beta/education/';
    
    $credentials = $this->db->select(['Credentials' => []])->where(['Credentials' => ['title' => 'sync']])->one();

    if ($credentials) {
      $this->bearer = $credentials['token'];
    }
    else {
      throw new Exception("Unauthorized", 3);
    }
  }
}
