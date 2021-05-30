<?php
/** 
* MS Teams user entity
* @author Oleg Zakirov
* @package Model\Entities\User
*/
namespace Model\Entities;

class User
{
  use \Library\Shared;
  use \Library\Entity;

  
  public static function search(String $guid = '', String $teams_guid = '', Int $id = 0, Int $limit = 1):self
  {
    $result = [];
    $db = self::getDB();
    $filters = [];
    
    $users = $db -> select(['Users' => []]);

    foreach (['id', 'guid', 'teams_guid'] as $filter) {
      if ($$filter) {
        $filters[$filter] = $$filter;
      }
    }

    if (!empty($filters)) {
      $users -> where(['Users' => $filters]);
    }
    
    foreach ($users->many($limit) as $user) {
      $class = __CLASS__;
      $result[] = new $class($user['guid'], $user['teams_guid'], id: $user['id']);
    }

    return $limit == 1 ? (isset($result[0]) ? $result[0] : null) : $result;
  }

  public function save():self {
    $db = $this->db;

    if (!$this->id) {
      $this->id = $db->insert([
        'Users' => ['guid' => $this->guid, 'teams_guid' => $this->teams_guid]
        ])->run(true)->storage['inserted'];
    }

    if ($this->_changed) {
      $db -> update('Users', $this->_changed)
          -> where(['Users' => ['id' => $this->id]])
          -> run();
    }

    return $this;
  }

  public function __construct(public String $teams_guid, public ?String $guid = '', public Int $id = 0) {
    $this->db = $this->getDB();
  }
}
