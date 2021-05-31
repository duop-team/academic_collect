<?php
/** 
* MS Teams assignment entity
* @author Oleg Zakirov
* @package Model\Entities\Assignment
*/
namespace Model\Entities;

class Assignment
{
  use \Library\Shared;
  use \Library\Entity;

  public static function search(String $guid = '', Int $team = 0, Int $id = 0, Int $limit = 1):self|null
  {
    $result = [];
    $db = self::getDB();
    $filters = [];
    
    $assignments = $db -> select(['Assignments' => []]);

    foreach (['id', 'guid', 'team'] as $filter) {
      if ($$filter) {
        $filters[$filter] = $$filter;
      }
    }

    if (!empty($filters)) {
      $assignments -> where(['Assignments' => $filters]);
    }
    
    foreach ($assignments->many($limit) as $assignment) {
      $class = __CLASS__;
      $result[] = new $class($assignment['guid'], $assignment['team'], id: $assignment['id']);
    }

    return $limit == 1 ? (isset($result[0]) ? $result[0] : null) : $result;
  }
  
  public function save():self {
    $db = $this->db;

    if (!$this->id) {
      $this->id = $db->insert([
        'Assignments' => ['guid' => $this->guid, 'team' => $this->team]
      ])->run(true)->storage['inserted'];
    }

    if ($this->_changed) {
      $db -> update('Assigments', $this->_changed)
          -> where(['Assignments' => ['id' => $this->id]])
          -> run();
    }

    return $this;
  }

  public function __construct(public String $guid, public Int $team, public Int $id = 0) {
    $this->db = $this->getDB();
  }
}
