<?php


class VideoLogManager extends Mapper {
  public function setLog(string $userid, string $emotion, int $video) {
    $sql = "INSERT INTO videologs (userid, emotion, video, created) VALUES (:userid, :emotion, :video, now())";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
    $stmt->bindParam(':emotion', $emotion, PDO::PARAM_STR);
    $stmt->bindParam(':video', $video, PDO::PARAM_INT);
    $stmt->execute();
  }

  public function getFinishedVideo(string $userid, string $emotion){
    $sql = "SELECT * FROM videologs WHERE (userid='$userid') AND (emotion='$emotion')";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
    $data = array();
    foreach($result as $r){
      array_push($data, $r["video"]);
    };
    return $data;
  }

  public function notContains(string $userid, string $emotion, array $video){
    $videoids = filter_var_array($video, FILTER_VALIDATE_INT);
    if(!$videoids){
      var_dump("Invalid request");
      return;
    }
    $inClause = substr(str_repeat(',?', count($videoids)), 1); // '?,?,?'
    $sql = "SELECT * FROM videologs WHERE (userid='$userid') AND (video NOT IN ($inClause)) AND (emotion='$emotion')";
    $stmt = $this->db->prepare($sql);
    $stmt->execute($videoids);
    $result = $stmt->fetchAll();
    $data = array();
    foreach($result as $r){
      array_push($data, $r["video"]);
    };
    return $data;
  }
}
