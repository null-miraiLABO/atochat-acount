<?php
// db設定,queryrunpre(),newdata()
require_once('../db.php');

//セッションスタート
session_name('sesname');
session_start();
session_regenerate_id(true);

// 各ファイルのパス
$HTML_FORM_DAT='form.dat';
$HTML_CHECK_DAT='check.dat';
$HTML_FIN_DAT='fin.dat';

if($_SERVER["REQUEST_METHOD"]=='POST'){

	if(isset($_POST['chk'])){
		$_POST['userid']=$_SESSION['userid'];
		$_POST['login']=$_SESSION['login'];
		$_SESSION=$_POST;
	}

	//押されたボタンによって次のページが何かをきめる
	$param='';
	if(isset($_POST['chk']))
		$param='?chk=1';
	else if(isset($_POST['fin']))
		$param='?fin=1';
	header("Location: " . $_SERVER['PHP_SELF'].$param);//$_SERVER['PHP_SELF']すなわち自分自身に接続しなおし
	exit();//ここでプログラム終了
}

//エラーチェック
$Err='';
//確認画面、終了画面を表示しようとしているときはエラーチェックします
if(isset($_GET['chk']) || isset($_GET['fin']))
{
	if(SeChk('pass')=='')
		$Err.='<div class="err">※パスワードを入力してください</div>';

	$db_id=$_SESSION["userid"];
	$db_pass=md5($_SESSION["pass"].$_SESSION["userid"]);
	$login_flag = queryrunpre("SELECT * FROM ".$dbtable." WHERE `mail`='".$db_id."' AND `pass`='".$db_pass."'",null);

	if(empty($login_flag)){
		$Err.='<div class="err">※PASSが一致しません</div>'.$db_pass;
	}

	if($Err!='')
		unset($_GET);
}

//まずはキーと中身をいれる変数初期化
$SearchKey=array();
$SearchValue=array();

foreach($_SESSION as $key=>$value){
	$SearchKey[]='{{'.$key.'}}';
	$SearchValue[]=htmlspecialchars($value);
}

//このプログラムではエラー処理を表示側でやっているので
//セッションにエラーがありません
//なのでそのぶんだけ追加
$SearchKey[]='{{Err}}';
$SearchValue[]=$Err;



//まずはdatの名前をいれる変数を用意して
$loadname="";
if(isset($_GET['chk'])){//確認画面だったら
	$loadname=$HTML_CHECK_DAT;//確認画面のdat名を控えます
}else if(isset($_GET['fin'])){
	$loadname=$HTML_FIN_DAT;

	//ip追加
	$usemail=str_replace('{{ip}}',$_SERVER['REMOTE_ADDR'],$usemail);
	$usemail=preg_replace("/{{.*?}}/","",$usemail);

	$dbwhere=" WHERE `mail` = :wheremail";
  $queryParam[":wheremail"]=$_SESSION["userid"];
  queryrunpre("DELETE FROM ".$dbtable.$dbwhere,$queryParam);

	$_SESSION["pass"]="";

	//何度も言うけど、本当はファイルロック処理をしないと
	//データが吹っ飛ぶ可能性があります。

	//書き込んだらセッション初期化しちゃいます
	//もういらないので。
	$_SESSION=array();
}else{
	$loadname=$HTML_FORM_DAT;
}

$usehtml=file_get_contents($loadname);

//ざっくり置き換え
$usehtml=str_replace($SearchKey,$SearchValue,$usehtml);
$usehtml=preg_replace("/{{.*?}}/","",$usehtml);

foreach($_SESSION as $key=>$value){
	echo "key: ".$key." => ";
	echo "val: ".$value."<br>";
}

$_SESSION['Err']='';

echo var_dump($SearchKey)."<br>";
echo var_dump($SearchValue)."<br>";
echo $usehtml;

//issetがメンチなので関数つくったった。
function SeChk($sessionName)
{
	return isset($_SESSION[$sessionName]) ? $_SESSION[$sessionName] : '';
}
?>