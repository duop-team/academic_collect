<?php
/** 
* MS Teams discipline entity
* @author Mykyta Ivanko
* @package Model\Entities\Discipline
*/
namespace Model\Entities;

class Discipline
{
  use \Library\Shared;
	use \Library\Entity;

  public static function search(String $guid = '', Int $id = 0, Int $limit = 1):self
  {
    $result = [];
    $db = self::getDB();
    $filters = [];

    $disciplines = $db -> select(['Disciplines' => []]);

    foreach (['id', 'guid'] as $filter) {
      if ($$filter) {
        $filters[$filter] = $$filter;
      }
    }

    if (!empty($filters)) {
      $disciplines -> where(['Disciplines' => $filters]);
    }

    foreach ($disciplines->many($limit) as $team) {
      $class = __CLASS__;
      $result[] = new $class($team['guid'], id: $team['id']);
    }

    return $limit == 1 ? (isset($result[0]) ? $result[0] : null) : $result;
  }

  public function save():self {
    $db = $this->db;

    if (!$this->id) {
      $this->id = $db->insert([
        'Disciplines' => ['guid' => $this->guid]
      ])->run(true)->storage['inserted'];
    }

    if ($this->_changed) {
      $db -> update('Disciplines', $this->_changed)
          -> where(['Disciplines' => ['id' => $this->id]])->run();
    }

    return $this;
  }

  public function __construct(public String $guid, public Int $id = 0) {
    $this->db = $this->getDB();
  }
}
