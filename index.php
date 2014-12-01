<?php
//TODO
//remove -i, remove /bin/bash , merge all installation in one
//echo $_POST['app_name']." ";
//unique app name is needed
//echo $_POST['lang']." ";
session_start();
$target_dir = "cloud_project/apps/";
$target_file = $target_dir . trim(basename($_FILES["appArchive"]["name"]));
$app_name = strtolower(str_replace(" ", "_", trim($_POST['app_name'])));
$lang = $_POST['lang'];

$file_name = basename($_FILES["appArchive"]["name"]);
$uploadOk = 1;
$allowedCompressedTypes = array("application/x-rar-compressed", "application/zip", "application/x-zip", "application/octet-stream", "application/x-zip-compressed");

if (in_array($_FILES["appArchive"]["type"], $allowedCompressedTypes)) {
    if (move_uploaded_file($_FILES["appArchive"]["tmp_name"], $target_file)) {
        if ($lang == 'php_mysql') {
            $user = $_POST['mysql_user'];
            $pass = $_POST['mysql_pass'];
            $sqlfile = basename($_FILES["mysqldump"]["name"]);
            if ($sqlfile && move_uploaded_file($_FILES["mysqldump"]["tmp_name"], $target_dir."../php_mysql/database.sql")) {
                $port = shell_exec('sudo bash ' . $target_dir . '../run.sh "' . $file_name . '" ' . $target_dir . '../ "' . $app_name . '" ' . $lang." ".$user." ".$pass);
            }
        } else {
            $port = shell_exec('sudo bash ' . $target_dir . '../run.sh "' . $file_name . '" ' . $target_dir . '../ "' . $app_name . '" ' . $lang);
        }//echo " The file ". basename( $_FILES["appArchive"]["name"]). " has been uploaded.";
    } else {
        //echo "Sorry, there was an error uploading your file.";
    }
}
$str = shell_exec("sudo docker ps -a | grep $app_name");
    if (strpos($str, "Exited ") !== false) {
        echo "Opps! something went wrong. Try again";
    } else {
        if (isset($_SESSION['loggedin'])) {
            include_once 'db_connect.php';
            $uid = $_SESSION['id'];
            mysql_query("INSERT INTO containers values($uid,'$app_name','<a href=\"http://$port\">$app_name</a>')");
            $_SESSION['containers'][] = array("container_name"=>"$app_name","app_url"=>"<a href=\"http://$port\">$app_name</a>");
            mysql_close($link);
        }
        echo $port;
    }

?>
