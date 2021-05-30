<?php
/** 
* MS Result result entity
* @author Mykyta Ivanko
* @package Model\Entities\Result
*/
namespace Model\Entities;

class Result {
  use \Library\Shared;
	use \Library\Entity;

  public static function search(Int $student = 0, Int $discipline_team = 0, Int $assignment = 0, Int $points = 0, String $submittedDate = '', Int $module = 0, Int $semester = 0,  Int $id = 0, Int $limit = 1):self
  {
    $result = [];
    $db = self::getDB();
    $filters = [];

    $results = $db -> select(['Results' => []]);

    foreach (['id', 'student', 'discipline_team', 'assignment', 'points', 'submittedDate', 'module', 'semester'] as $filter) {
      if ($$filter) {
        $filters[$filter] = $$filter;
      }
    }

    if (!empty($filters)) {
      $results -> where(['Results' => $filters]);
    }

    foreach ($results->many($limit) as $res) {
      $class = __CLASS__;
      $result[] = new $class(
        $res['student'], $res['discipline_team'], $res['assignment'], $res['points'],
        $res['submittedDate'], $res['module'], $res['semester'],  id: $res['id']
      );
    }

    return $limit == 1 ? (isset($result[0]) ? $result[0] : null) : $result;
  }

  public function save():self {
    $db = $this->db;

    if (!$this->id) {
      $this->id = $db->insert([
        'Results' => [
          'student' => $this->student, 'discipline_team' => $this->discipline_team, 'assignment' => $this->assignment,
          'points' => $this->points, 'submittedDate' => $this->submittedDate, 'module' => $this->module, 'semester' => $this->semester
        ]
      ])->run(true)->storage['inserted'];
    }

    if ($this->_changed) {
      $db -> update('Results', $this->_changed)
          -> where(['Results' => ['id' => $this->id]])
          -> run();
    }

    return $this;
  }

  public function __construct(public Int $student, public Int $discipline_team, public Int $assignment, public Int $points, public String $submittedDate, public Int $module = 0, public Int $semester = 0, public Int $id = 0) {
    $this->db = $this->getDB();
  }
}