<?php

require __DIR__ . '/../config.php';
require __DIR__ . '/../db.class.php';

require __DIR__ . '/check.php';

$db = MySqlii::getInstance();
$act = $_REQUEST['act'];

$current_admin_uid = $_SESSION['user_id']; // 当前后台管理人员用户ID
$ret = [];

// 新增节点
if ($act == 'insert') {
    $title = $_REQUEST['title'];
    $did = $_REQUEST['did'];
    $prid = $_REQUEST['prid'];

    if ( is_null($title) || is_null($did) || is_null($prid) ) {
        echo json_encode(['status'=>'FAIL', 'msg'=>'缺少必要的参数值!']);
        exit;
    }

    $create_time = $update_time = date('Y-m-d H:i:s');

    $sql = "INSERT INTO " . DB_PREFIX . "article(article_title, article_content, parent_id, project_id, last_edit_uid, 
            create_time, update_time) VALUES('{$title}', '', $did, $prid, $current_admin_uid, '{$create_time}', '{$update_time}')";
    $db->query($sql);
    $insert_id = $db->insert_id();
    $ret = ['status'=>'SUCC', 'did'=>$insert_id, 'msg'=>'添加成功!'];
}

// 更新节点
if ($act == 'update') {
    $title = $_REQUEST['title'];
    $did = $_REQUEST['did'];

    if ( is_null($title) || is_null($did) ) {
        echo json_encode(['status'=>'FAIL', 'msg'=>'缺少必要的参数值!']);
        exit;
    }

    $update_time = date('Y-m-d H:i:s');
    $title = addslashes($title);
    $sql = "UPDATE " . DB_PREFIX . "article SET article_title='{$title}', update_time='{$update_time}' WHERE id=" . $did;
    $db->query($sql);
    $ret = ['status'=>'SUCC', 'msg'=>'操作成功!'];
}

// 删除节点
if ($act == 'delete') {
    $did = $_REQUEST['did'];

    if ( is_null($did) ) {
        echo json_encode(['status'=>'FAIL', 'msg'=>'缺少必要的参数值!']);
        exit;
    }

    $sql = "DELETE FROM " . DB_PREFIX . "article WHERE id=" . $did;
    $db->query($sql);
    $ret = ['status'=>'SUCC', 'msg'=>'操作成功!'];
}

// 获取文章内容
if ($act == 'get_article_content') {
    $did = $_REQUEST['did'];

    if ( is_null($did) ) {
        echo json_encode(['status'=>'FAIL', 'msg'=>'缺少必要的参数值!']);
        exit;
    }
    $sql = 'SELECT article_content FROM ' . DB_PREFIX . 'article WHERE id=' . $did;
    $query = $db->query($sql);
    $row = $db->fetch_array($query);
    $content = $row['article_content'];
    $ret = ['status'=>'SUCC', 'msg'=>'查询成功!', 'content'=>$content];
}

// 保存文章内容
if ($act == 'save_article_content') {
    $did = $_REQUEST['did'];
    $node_type = $_REQUEST['node_type'];
    $update_time = date('Y-m-d H:i:s');
    $article_content = addslashes($_REQUEST['article_content']);

    if ( is_null($did) || is_null($node_type) || is_null($article_content) ) {
        echo json_encode(['status'=>'FAIL', 'msg'=>'缺少必要的参数值!']);
        exit;
    }

    $sql = "UPDATE " . DB_PREFIX . "article SET article_content='{$article_content}', node_type=$node_type WHERE id=" . $did;
    $db->query($sql);
    $ret = ['status'=>'SUCC', 'msg'=>'操作成功!'];
}

// 创建项目
if ($act == 'create_project') {
    $project_name = $_REQUEST['project_name'];
    $project_description = $_REQUEST['project_description'];
    $project_name =  addslashes($project_name);
    $project_description = addslashes($project_description);

    if ( is_null($project_name) || is_null($project_description) ) {
        echo json_encode(['status'=>'FAIL', 'msg'=>'缺少必要的参数值!']);
        exit;
    }

    $create_time = $update_time = date('Y-m-d H:i:s');

    $sql = "INSERT INTO " . DB_PREFIX . "project(project_name, project_description, user_id, 
            create_time, update_time) VALUES('{$project_name}', '{$project_description}', $current_admin_uid, '{$create_time}', '{$update_time}')";
    $db->query($sql);
    $insert_id = $db->insert_id();
    $ret = ['status'=>'SUCC', 'pid'=>intval($insert_id), 'msg'=>'添加成功!'];
}

// 删除项目
if ($act == 'delete_project') {
    $pid = $_REQUEST['pid'];

    if ( is_null($pid) ) {
        echo json_encode(['status'=>'FAIL', 'msg'=>'缺少必要的参数值!']);
        exit;
    }

    $sql = "DELETE FROM " . DB_PREFIX . "project WHERE id=" . $pid;
    $db->query($sql);
    $sql = "DELETE FROM " . DB_PREFIX . "article WHERE project_id=" . $pid;
    $db->query($sql);
    $ret = ['status'=>'SUCC', 'msg'=>'操作成功!'];
}

if ( empty($ret) ) {
    $ret = ['status'=>'FAIL', 'msg'=>'操作失败, 请检查act参数值是否正确!'];
}

echo json_encode($ret);
exit;