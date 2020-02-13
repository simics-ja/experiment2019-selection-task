<?php


class UserManager extends Mapper {
  public function getId(string $userid) {
    $sql = "select id from users where userid = '" . $userid . "'";
    $stmt = $this->db->query($sql);
    $results = [];
    while($row = $stmt->fetch()){
      $results[] = $row;
    }
    return $results;
  }
}