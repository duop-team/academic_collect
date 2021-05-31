<?php
/** 
* MS Teams class entity
* @author Mykyta Ivanko
* @package Model\Entities\Team
*/
namespace Model\Entities;

class Team
{
  use \Library\Shared;
	use \Library\Entity;

  public static function search(String $guid = '', Int $id = 0, Int $limit = 1):self|null
  {
    $result = [];
    $db = self::getDB();
    $filters = [];

    $teams = $db -> select(['Teams' => []]);

    foreach (['id', 'guid'] as $filter) {
      if ($$filter) {
        $filters[$filter] = $$filter;
      }
    }

    if (!empty($filters)) {
      $teams -> where(['Teams' => $filters]);
    }

    foreach ($teams->many($limit) as $team) {
      $class = __CLASS__;
      $result[] = new $class($team['guid'], id: $team['id']);
    }

    return $limit == 1 ? (isset($result[0]) ? $result[0] : null) : $result;
  }

  public function save():self {
    $db = $this->db;

    if (!$this->id) {
      $this->id = $db->insert([
        'Teams' => ['guid' => $this->guid]
      ])->run(true)->storage['inserted'];
    }

    if ($this->_changed) {
      $db -> update('Teams', $this->_changed)
          -> where(['Teams' => ['id' => $this->id]])
          -> run();
    }

    return $this;
  }

  public function __construct(public String $guid, public Int $id = 0) {
    $this->db = $this->getDB();
  }
}
