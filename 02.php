<?php
$HTML_02_DAT='src/02.dat';

//セッションスタート
session_name('sesname');
session_start();
session_regenerate_id(true);

$stetus="";
if($_SESSION["login"]=="login"){
  $stetus=$_SESSION["userid"]."さん、ようこそ。";
}else{
  $stetus="ログインしてません";
}

$usehtml=file_get_contents($HTML_02_DAT);
$usehtml=str_replace("{{ステータス}}",$stetus,$usehtml);

echo $usehtml;
?>