<?php

class VideoAssessment extends Mapper {
  public function setLog(string $username, int $id) {
    $sql = "INSERT INTO video (username, finished_id, created) VALUES (:username, :finished_id, now())";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':finished_id', $id, PDO::PARAM_INT);
    $stmt->execute();
  }
  public function getLog(string $username) {
    $sql = "SELECT * from video WHERE username='$username'";
    $stmt = $this->db->query($sql);
    $results = [];
    while($row = $stmt->fetch()){
      $results[] = $row;
    }
    return $results;
  }
}