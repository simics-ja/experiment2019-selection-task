<?php

use Slim\Http\Request;
use Slim\Http\Response;


// Routes

$app->get('/task-list/[{userid}]', function (Request $request, Response $response, array $args) {
    $userid =$args["userid"];
    $userLogDB = new LogManager($this->db);
    $conditions = array(
        "pp" => "https://s2-lab.sakura.ne.jp/shimizu/selection-task/public/video/$userid/pp",
        "pn" => "https://s2-lab.sakura.ne.jp/shimizu/selection-task/public/video/$userid/pn",
        "pm" => "https://s2-lab.sakura.ne.jp/shimizu/selection-task/public/video/$userid/pm",
        "pb" => "https://s2-lab.sakura.ne.jp/shimizu/selection-task/public/video/$userid/pb",
        "np" => "https://s2-lab.sakura.ne.jp/shimizu/selection-task/public/video/$userid/np",
        "nn" => "https://s2-lab.sakura.ne.jp/shimizu/selection-task/public/video/$userid/nn",
        "nm" => "https://s2-lab.sakura.ne.jp/shimizu/selection-task/public/video/$userid/nm",
        "nb" => "https://s2-lab.sakura.ne.jp/shimizu/selection-task/public/video/$userid/nb",
        "bp" => "https://s2-lab.sakura.ne.jp/shimizu/selection-task/public/video/$userid/bp",
        "bn" => "https://s2-lab.sakura.ne.jp/shimizu/selection-task/public/video/$userid/bn",
        "bm" => "https://s2-lab.sakura.ne.jp/shimizu/selection-task/public/video/$userid/bm",
        "bb" => "https://s2-lab.sakura.ne.jp/shimizu/selection-task/public/video/$userid/bb",
    );

    $finished_conditions = $userLogDB->getUserLog($userid);
    $display_conditions = array_diff(array_keys($conditions), $finished_conditions);
    foreach($conditions as $condition => $url){
        if(!in_array($condition, $display_conditions)){
            unset($conditions[$condition]);
        }
    }
    $data = array("conditions" => $conditions);
    return $this->view->render($response, 'task-list.html', $data);
})->setName('task-list');

$app->get('/video[/{userid}[/{condition}]]', function (Request $request, Response $response, array $args) {
    $userid = $args["userid"];
    $condition = $args["condition"];
    $videoCondition = substr($condition, 0, 1);
    $uiCondition = substr($condition, 1, 1);

    $videos = array(
        "https://www.youtube.com/embed/dLLRg0sur6g",
        "https://www.youtube.com/embed/rdvabdyZ7vU",
        "https://www.youtube.com/embed/5Gs48yH8tCM",
        "https://www.youtube.com/embed/tgfGiTA1pek",
        "https://www.youtube.com/embed/XZ9Cf_8A6jE?start=4183&end=4321",
        "https://www.youtube.com/embed/UMThhQ0OuFg",
        "https://www.youtube.com/embed/CcM3zDgIWK0",
        "https://www.youtube.com/embed/5uB7wLb3Vsw",
        "https://www.youtube.com/embed/CniVmgyR0yY",
        "https://www.youtube.com/embed/D21z_ppvwz8",
        "https://www.youtube.com/embed/3HMCEtus3fI",
        "https://www.youtube.com/embed/IcSY2VBUicc",
    );
    $videologdb = new VideoLogManager($this->db);
    $candidate = array_rand(array_diff(array(0,1,2,3), $videologdb->getFinishedVideo($userid, $videoCondition)));
    switch ($videoCondition) {
        case 'p':
            $index = $candidate;
            break;
        case 'n':
            $index = 4 + $candidate;
            break;
        case 'b':
            $index = 8 + $candidate;
            break;
        default:
            return;
            break;
    }
    $videologdb->setLog($userid, $videoCondition, $candidate);
    $data = array(
        "videourl" => $videos[$index],
        "testurl" => "/test/$userid/$videoCondition/$uiCondition/0"
    );
    return $this->view->render($response, 'video-view.html', $data);
})->setName('video-view');

$app->map(['GET', 'POST'], '/test[/{params:.*}]', function (Request $request, Response $response, array $args) {
    $postParams = $request->getParsedBody();

    $items = array("t", "k", "c", "n");
    shuffle($items);

    if(!is_null($postParams)){
        $userdb = new LogManager($this->db);
        $userdb->setLog($postParams["userid"], $postParams["video"], $postParams["ui"], $postParams["answer"], implode(",", $items));
    }

    $params = explode('/', $args['params']);
    $userid = $params[0];
    $video = $params[1];
    $ui = $params[2];
    $trial_count = $params[3];
    //$ui = substr($condition, -1);
    //$trial_count = $params[2];

    $data = array(
        "userid" => $userid,
        "video" => $video,
        "ui" => $ui,
        "trial_count" => $trial_count+1,
        "items" => $items,
    );
    if($trial_count < 25) {
        return $this->view->render($response, 'test.html', $data);
    }else{
        return $this->view->render($response, 'finish.html', $data);
    }
})->setName('video-view');

$app->get('/users/[{userid}]', function (Request $request, Response $response, array $args) {
    // // Sample log message
    // $this->logger->info("Slim-Skeleton '/' route");
    $userid = $args['userid'];
    $userdb = new UserManager($this->db);
    $test_type = intval($userdb->getId($userid)) % 4;

    $data = array('userid' => $userid, 'label1' => 'いろんな味が楽しめる', 'label2' => '王道の味', 'label3' => '歯ごたえが最高', 'label4' => '意外性を求めるあなたに');

    // Render index view
    return $this->view->render($response, 'home.html', $data);
})->setName('home');

$app->map(['GET', 'POST'], '/video-list[/{params:.*}]', function (Request $request, Response $response, array $args) {
    $va = new VideoAssessment($this->db);
    $params = explode('/', $args['params']);
    if(count($params) == 1) {
        $name = $params[0];
        $postParams = $request->getParsedBody();
        if(!is_null($postParams)){
            $va->setLog($name, $postParams["videoid"]);
        }

        $logs = $va->getLog($name);
        $finished = array();
        foreach($logs as $log){
            $index = (int) $log["finished_id"];
            $finished[] = $index;
        }

        $data = array(
            "name" => $name,
            "logs" => $finished,
        );
        return $this->view->render($response, 'video-list.html', $data);
    }else if(count($params) == 2) {
        $videoUrls = array(
            'https://www.youtube.com/embed/GBxCIy92bP8',
            'https://www.youtube.com/embed/M_m8u_fwGTE',
            'https://www.youtube.com/embed/M0PBzKGPvNI',
            'https://www.youtube.com/embed/YNublPtUoKU',
            'https://www.youtube.com/embed/dLLRg0sur6g',
            'https://www.youtube.com/embed/gnrQKBrNp_0',
            'https://www.youtube.com/embed/rdvabdyZ7vU',
            'https://www.youtube.com/embed/Hg8d2rxRIB4',
            'https://www.youtube.com/embed/5Gs48yH8tCM',
            'https://www.youtube.com/embed/tgfGiTA1pek',
            'https://www.youtube.com/embed/mIb5A3Ghako',
            'https://www.youtube.com/embed/XZ9Cf_8A6jE?start=4183&end=4321',
            'https://www.youtube.com/embed/5pErtY4z3FA',
            'https://www.youtube.com/embed/guRFH4qgWyw',
            'https://www.youtube.com/embed/swQyzgNPUKE',
            'https://www.youtube.com/embed/UMThhQ0OuFg',
            'https://www.youtube.com/embed/CcM3zDgIWK0',
            'https://www.youtube.com/embed/0eXsQo-CpOg',
            'https://www.youtube.com/embed/5uB7wLb3Vsw',
            'https://www.youtube.com/embed/-GZP3N2IacM',
            'https://www.youtube.com/embed/CniVmgyR0yY',
            'https://www.youtube.com/embed/D21z_ppvwz8',
            'https://www.youtube.com/embed/0Jci7V7Hl34',
            'https://www.youtube.com/embed/6wBpijX0qt4',
            'https://www.youtube.com/embed/W7SuyuZdt_I',
            'https://www.youtube.com/embed/3HMCEtus3fI',
            'https://www.youtube.com/embed/uW-B1tdkFsI',
            'https://www.youtube.com/embed/_j6wzdCvn0s',
            'https://www.youtube.com/embed/tEcmw_wMsAI',
            'https://www.youtube.com/embed/IcSY2VBUicc',
        );
        $name = $params[0];
        $videoid = $params[1];
        $videoUrl = $videoUrls[$videoid];
        $data = array(
            "name" => $name,
            "videoid" => $videoid,
            "videourl" => $videoUrl
        );
        return $this->view->render($response, 'video-assessment.html', $data);
    }
})->setName('video-list');