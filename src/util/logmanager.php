<?php


class LogManager extends Mapper {
  public function setLog(string $userid, string $video, string $ui, string $answer, string $item_order) {
    $sql = "INSERT INTO userlogs (userid, video, ui, answer, item_order, created) VALUES (:userid, :video, :ui, :answer, :item_order, now())";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
    $stmt->bindParam(':video', $video, PDO::PARAM_STR);
    $stmt->bindParam(':ui', $ui, PDO::PARAM_STR);
    $stmt->bindValue(':answer', $answer, PDO::PARAM_STR);
    $stmt->bindParam(':item_order', $item_order, PDO::PARAM_STR);
    $stmt->execute();
  }

  public function countRow(string $userid, string $video, string $ui){
    $sql = "SELECT * FROM userlogs WHERE userid='$userid' AND video='$video' AND ui='$ui'";
    $stmt = $this->db->query($sql);
    $count = $stmt->rowCount();
    return $count;
  }
  public function getUserLog(string $userid){
    $sql = "SELECT * FROM userlogs WHERE (userid='$userid')";
    $stmt = $this->db->query($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
    $data = array();
    foreach($result as $r){
      array_push($data, $r["video"].$r["ui"]);
    };
    
    return array_unique($data);
  }

}