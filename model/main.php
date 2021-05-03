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
		$url = $this->teams->authUrl();

		// printme($url);
		header("Location: $url");
	}

	public function teamssetup(String $code, String $error):?array {
		$result = [];

		if ($error) {
			throw new Exception($error, 6);
		}

		$this->teams->setup($code);

		header("Location: /teams/me");

		return null;
	}

	public function teamsrefresh()
	{
		$this->teams->refresh();
	}

	public function teamsme()
	{
		return $this->teams->me();
	}

	public function statsget():?array {
		$class = "7ec53ea3-ace8-4d02-8bea-cd024a149c2a";
		$assignment = "5d0884c6-00b6-4c96-919a-f2aa2b9442b5";
		$submission = "5ecca75a-4f8d-b094-2f7b-990106d4e2ab";

		$className = $this->teams->team($class)['displayName'];
		$assignmentTitle = $this->teams->assignment($class, $assignment)['displayName'];
		$points = $this->teams->points($class, $assignment, $submission);

		return [
			'discipline' => $className,
			'assignment' => $assignmentTitle,
			'grade' => $points
		];
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

		$this->teams = new Services\Teams();
	}
}