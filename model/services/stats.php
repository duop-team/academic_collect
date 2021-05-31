<?php
/**
 * Student stats service
 *
 * @author Yuriy Dmitriev, Oleg Zakirov, Vadim Savkunov
 * @package Model\Services\Stats
 */
namespace Model\Services;

class Stats {
  public function cache(String $student) {
    $user = null;
    $teams = [];
		$assignments = [];
		$results = [];
		$total = null;

    $user = \Model\Entities\User::search(teams_guid: $student);
    if (!$user) {
      $user = new \Model\Entities\User($student);
      $user->save();
      $user = \Model\Entities\User::search(teams_guid: $student);
    }

    foreach ($this->teams->listTeams($user->teams_guid) as $team) {
      $temp = \Model\Entities\Team::search(guid: $team["id"]);

      if (!$temp) {
        $temp = new \Model\Entities\Team($team["id"]);
        $temp->save();
        $temp = \Model\Entities\Team::search(guid: $team["id"]);
      }

      array_push($teams, $temp);
    }

    foreach ($teams as $class) {
      foreach ($this->teams->listAssignments($class->guid) as $assignment) {
        $temp = \Model\Entities\Assignment::search(guid: $assignment['id'], team: $class->id);
        if (!$temp) {
          $temp = new \Model\Entities\Assignment($assignment['id'], $class->id);
          $temp->save();
          $temp = \Model\Entities\Assignment::search(guid: $assignment['id'], team: $class->id);
        }

        array_push($assignments, $temp);
      }

      foreach ($assignments as $item) {
        $submission = $this->teams->getSubmission($class->guid, $item->guid, $user->teams_guid);

        if ($submission) {
          $points = $this->teams->getPoints($class->guid, $item->guid, $submission['id']);
        }

        if (!$points) {
          continue;
        }

        $temp = \Model\Entities\Result::search(student: $user->id, discipline_team: $class->id, assignment: $item->id);
        if (!$temp) {
          $temp = new \Model\Entities\Result(student: $user->id, discipline_team: $class->id, assignment: $item->id, points: $points, submittedDate: $submission['submittedDateTime']);
          $temp->save();
          $temp = \Model\Entities\Result::search(student: $user->id, discipline_team: $class->id, assignment: $item->id);
        }
      }
    }
  }

  public function get(String $student, String $discipline):?array {
		$total = null;
    $user = \Model\Entities\User::search(teams_guid: $student);
		$team = \Model\Entities\Team::search(guid: $discipline);
		$result = \Model\Entities\Result::search(student: $user->id, discipline_team: $team->id);
		foreach ($result as $res) {
			$total += $res->points; 
		}
		return [
			'student_guid' => $user->teams_guid,
			'discipline_guid' => $discipline,
			'points' => $total
		];
	}

  public function insert(String $student, String $discipline, String $assignment, Int $points) {
    $user = \Model\Entities\User::search(teams_guid: $student);
		$team = \Model\Entities\Team::search(guid: $discipline);
		$assignm = \Model\Entities\Assignment::search(guid: $assignment, team: $team->id);
		$result = new \Model\Entities\Result($user->id, $team->id, $assignm->id, $points);
		$result->save();

		return [$result];
  }

  public function __construct() {
    $this->teams = new Teams();
  }
}