<?php
/**
 * User Controller
 *
 * @author Serhii Shkrabak
 * @global object $CORE->model
 * @package Model\Main
 */
namespace Model;
class Main
{
	use \Library\Shared;
	use \Library\Uniroad;

	public function uniwebhook(String $type = '', String $value = '', Int $code = 0):?array {
		$result = null;
		switch ($type) {
			case 'message':
				if ($value == 'вихід') {
					$result = ['type' => 'context', 'set' => null];
				} else 
					$result = [
						'to' => $GLOBALS['uni.user'],
						'type' => 'message',
						'value' => "Сервіс `Збір даних академічної успішності` отримав повідомлення $value"
					];
				break;
				case 'click':
					$result = [
						'to' => $GLOBALS['uni.user'],
						'type' => 'message',
						'value' => "Сервіс `Збір даних академічної успішності`. Натиснуто кнопку $code",
						'keyboard' => [
							'inline' => false,
							'buttons' => [
								[['id' => 9, 'title' => 'Надати номер', 'request' => 'contact']]
							]
						]
					];
					break;
				case 'contact':
					$result = [
						'to' => $GLOBALS['uni.user'],
						'type' => 'message',
						'value' => "Сервіс `Збір даних академічної успішності`. Отримано номер $value"
					];
					break;
		}

		return $result;
	}

	public function formsubmitAmbassador(String $firstname, String $secondname, String $phone, String $position = ''):?array {
		$result = null;
		$chat = 891022220;
		$this->TG->alert("Нова заявка в *Цифрові Амбасадори*:\n$firstname $secondname, $position\n*Зв'язок*: $phone");
		$result = [];
		return $result;
	}

	public function teamsauth()
	{
		$tenant = $this->getVar('TENANT', 'e');
		$client = $this->getVar('CLIENT', 'e');
		$scope = $this->getVar('SCOPE', 'e');

		$url = "https://login.microsoftonline.com/$tenant/oauth2/v2.0/authorize/?client_id=$client&scope=$scope&response_type=code";

		header("Location: $url");
	}

	public function teamssetup(String $code, String $error):?array {
		$result = [];

		if ($error) {
			throw new Exception($error, 6);
		}

		$tenant = $this->getVar('TENANT', 'e');
		$client = $this->getVar('CLIENT', 'e');
		$scope = $this->getVar('SCOPE', 'e');
		$secret = $this-> getVar('SECRET', 'e');

		$data = [
			'client_id' => $client,
			'scope' => $scope,
			'grant_type' => 'authorization_code',
			'client_secret' => $secret,
			'redirect_uri' => 'https://academic.pnit.od.ua/teams/setup',
			'code' => $code
		];

		$url = "login.microsoftonline.com/$tenant/oauth2/v2.0/token";
		// $responce = $this->request($url, $data);
		// $result = json_decode($responce, true);

		// return [$this->db->createTable('Credentials', [
		// 	'id' => [
		// 		'type' => 'int',
		// 		'attributes' => ['unsigned'],
		// 		'autoincrement' => true,
		// 		'key' => 'primary'
		// 	],
		// 	'title' => [
		// 		'type' => 'varchar(20)'
		// 	],
		// 	'token' => [
		// 		'type' => 'text',
		// 	],
		// 	'refresh' => [
		// 		'type' => 'text'
		// 	]
		// ])];

		// return [$this->db->select(['credentials' => []])->result];

		return [true];
	}

	public function statsget():?array {
		return ['hello world'];
	}

	public function __construct() {
		$this->db = new \Library\MySQL('core',
			\Library\MySQL::connect(
				$this->getVar('DB_HOST', 'e'),
				$this->getVar('DB_USER', 'e'),
				$this->getVar('DB_PASS', 'e')
			) );
		$this->setDB($this->db);
		// $this -> TG = new Services\Telegram(key: $this->getVar('TGKey', 'e'), emergency: 280751679);
	}
}