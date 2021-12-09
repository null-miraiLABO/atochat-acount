<?php
// db設定,queryrunpre(),newdata()
require_once('db.php');

$HTML_MAIN_DAT='src/main.dat';
$HTML_FORM_DAT='src/form.dat';

//セッションスタート
session_name('sesname');
session_start();
session_regenerate_id(true);

$_SESSION["stetus"]="ind";

//logout
if($_GET["logout"]==1){
  $_SESSION=array();
  header("Location: " . $_SERVER['PHP_SELF']);//$_SERVER['PHP_SELF']すなわち自分自身に接続しなおし
	exit();//ここでプログラム終了
}

//指定されたdatを読み込んで
$usehtml=file_get_contents($HTML_MAIN_DAT);
$formhtml=file_get_contents($HTML_FORM_DAT);

if($_SERVER["REQUEST_METHOD"]=='POST')
{
  $_SESSION["userid"]=$_POST["userid"];

  if($_SESSION["userid"]==""){
    $_SESSION["Err"].='<div class="err">※IDを入力してください</div>';
  }
  if($_POST["passwd"]==""){
    $_SESSION["Err"].='<div class="err">※PASSを入力してください</div>';
  }


  $db_id=$_SESSION["userid"];
  $db_pass=md5($_POST["passwd"].$_SESSION["userid"]);
  $login_flag = queryrunpre("SELECT * FROM ".$dbtable." WHERE `mail`='".$db_id."' AND `pass`='".$db_pass."'",null);

  if($_SESSION["Err"]==""){
    if(empty($login_flag)){
      $_SESSION["Err"].='<div class="err">※PASSかIDが一致しません</div>';
    }
  }

  if($_SESSION["Err"]==""){
    $_SESSION["login"]="login";
  }

  header("Location: " . $_SERVER['PHP_SELF']);//$_SERVER['PHP_SELF']すなわち自分自身に接続しなおし
	exit();//ここでプログラム終了
}

//ざっくり置き換え
if($_SESSION["login"] && $_SESSION["login"]=="login"){
  $usehtml=str_replace("{{ログインフォーム}}",'<a href="?logout=1">ログアウト</a>&nbsp;|&nbsp;<a href="./delete">退会</a>',$usehtml);
  $usehtml=str_replace("{{ステータスメッセージ}}",$_SESSION["userid"]."さん、ようこそ。",$usehtml);
}else{
  $usehtml=str_replace("{{ログインフォーム}}",$formhtml,$usehtml);
  $usehtml=str_replace("{{Err}}",$_SESSION["Err"],$usehtml);
  $_SESSION["Err"]="";
  $usehtml=str_replace("{{userid}}",$_SESSION["userid"],$usehtml);
  $usehtml=str_replace("{{ステータスメッセージ}}","ログインしていません",$usehtml);
}

$usehtml=preg_replace("/{{.*?}}/","",$usehtml);

echo $usehtml;
?>