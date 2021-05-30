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

  public static function search(String $guid = '', Int $id = 0, Int $limit = 1):self
  {
    $result = [];
    $db = self::getDB();
    $filters = [];
    
    $assignments = $db -> select(['Assignments' => []]);

    foreach (['id', 'guid'] as $filter) {
      if ($$filter) {
        $filters[$filter] = $$filter;
      }
    }

    if (!empty($filters)) {
      $assignments -> where(['Assignments' => $filters]);
    }
    
    foreach ($assignments->many($limit) as $assignment) {
      $class = __CLASS__;
      $result[] = new $class($assignment['guid'], id: $assignment['id']);
    }

    return $limit == 1 ? (isset($result[0]) ? $result[0] : null) : $result;
  }
  
  public function save():self {
    $db = $this->db;

    if (!$this->id) {
      $this->id = $db->insert([
        'Assignments' => ['guid' => $this->guid]
      ])->run(true)->storage['inserted'];
    }

    if ($this->_changed) {
      $db -> update('Assigments', $this->_changed)
          -> where(['Assignments' => ['id' => $this->id]])
          -> run();
    }

    return $this;
  }

  public function __construct(public String $guid, public Int $id = 0) {
    $this->db = $this->getDB();
  }
}
