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
  private String $redirectUri;
  private String $baseUrl;

  private Array $authUri;

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
    $result = 0;
    $outcome = $this->get("classes/$team/assignments/$assignment/submissions/$submission/outcomes")['value'];
    foreach ($outcome as $rubric) {
      if (isset($rubric['publishedPoints'])) {
        $result = $rubric['publishedPoints']['points'];
        break;
      }
    }
    return $result;
  }

  public function me()
  {
    $result = null;
    $result = $this->get('me');
    return $result;
  }

  public function setup(String $code) {
    $this->token($code, 'authorization_code');
  }

  public function refresh() {
    $code = '';

    $query = $this->db->select(['Credentials' => ['refresh']])
    ->where(['Credentials' => ['title' => 'sync']])->one();

    $code = $query['refresh'];

    $this->token($code, 'refresh_token');
  }

  private function token(String $code, String $grantType) {
    // $url = "login.microsoftonline.com/{$this->tenant}/oauth2/v2.0/token";
    $url = "login.microsoftonline.com/". $this->tenant ."/oauth2/v2.0/token";
    $data = array(
      'client_id' => $this->client,
      'client_secret' => $this->secret,
      'redirect_uri' => $this->redirectUri,
      'scope' => $this->scope,
      'grant_type' => $grantType
    );

    switch ($grantType) {
      case 'authorization_code':
        $data['code'] = $code;
        break;

      case 'refresh_token':
        $data['refresh_token'] = $code;
        break;
      
      default:
        throw new \Exception("Unknown grant_type value", 6);
        break;
    }
    
    $response = $this->request($url, $data);
    $result = json_decode($response, true);
    // var_dumps($result);
    
    $this->bearer = $result['access_token'];    

    $credentials = $this->db->select(['Credentials' => []])
									->where(['Credentials' => ['title' => 'sync']])->one();
		if ($credentials) {
			$this->db->update('Credentials', [
				'token' => $result['access_token'], 
				'refresh' => $result['refresh_token']
			])->where(['Credentials' => ['title' => 'sync']])->run();
		} 
		else {
			$this->db->insert(['Credentials' => [
				'title' => 'sync',
				'token' => $result['access_token'],
				'refresh' => $result['refresh_token']
			]])->run();
		}
  }

  public function authUrl():String
  {
    if($this->isAuthorized()) {
      throw new \Exception("Sync account already set up", 3);
    }

    $url = "https://login.microsoftonline.com/{$this->tenant}/oauth2/v2.0/authorize/?client_id={$this->client}&scope={$this->scope}&response_type=code";
    return $url;
  }

  private function isAuthorized()
  {
    $status = $this->db->select(['Credentials' => []])->where(['Credentials' => ['title' => 'sync']])->one();

    return !!$status;
  }

  public function __construct() {
    $this->db = $this->getDB();
    $this->tenant = $this->getVar('TENANT', 'e');
		$this->client = $this->getVar('CLIENT', 'e');
		$this->scope = $this->getVar('SCOPE', 'e');
		$this->secret = $this->getVar('SECRET', 'e');
    $this->redirectUri = "https://{$this->getVar('HTTP_HOST', 'e')}/teams/setup";
    $this->authUri = array('/teams/auth', '/teams/setup');
    $this->baseUrl = 'https://graph.microsoft.com/beta/education/';
    
    $url = $this->getVar('REQUEST_URI', 'e');
    $url = explode('?', $url)[0];
    $credentials = $this->db->select(['Credentials' => []])->where(['Credentials' => ['title' => 'sync']])->one();

    if ($credentials) {
      $this->bearer = $credentials['token'];
    }
    else {
      if (!in_array($url, $this->authUri)) {
        throw new \Exception("Unauthorized", 3);
      }
    }
  }
}
