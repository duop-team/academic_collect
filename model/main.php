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

	public function teamsrefresh():void {
		$this->teams->refresh();
	}

	public function statsget(String $student, String $discipline):?array {
		return $this->stats->get($student, $discipline);
	}

	public function statsinsert(String $student, String $discipline, String $assignment, Int $points) {
		return $this->stats->insert($student, $discipline, $assignment, $points);
	}

	public function statscache(String $student):void
	{
		$this->stats->cache($student);
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
		$this->stats = new Services\Stats();
	}
}