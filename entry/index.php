<?php
// db設定,queryrunpre(),newdata()
require_once('../db.php');

//セッションスタート
session_name('sesname');
session_start();
session_regenerate_id(true);

//問い合わせ送り先
$ownermail='tmc20247006@gmail.com';
//問い合わせ件名
$mailsub='お問い合わせ';
//メール用dat
$MAIL_DAT='mailvalue.dat';

// 各ファイルのパス
$HTML_FORM_DAT='form.dat';
$HTML_CHECK_DAT='check.dat';
$HTML_FIN_DAT='fin.dat';

//$LOGNAME='log/enq.log';
$LOGTEMP='log.dat';

if($_SERVER["REQUEST_METHOD"]=='POST'){

	if(isset($_POST['chk'])){
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
	//SeChk()を使っていちいちissetをかかずエラーをチェックしてます
	//エラーチェックは必ずセッションに入っているデータからやりましょ。
	if(SeChk('name')=='')
		$Err.='<div class="err">※名前を入力してください</div>';

	if(SeChk('mail')=='')
		$Err.='<div class="err">※メールアドレスを入力してください</div>';
	else if(!preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\.+_-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/',SeChk('mail')) )
		$Err.='<div class="err">※メールアドレスを確認してください</div>';
	else
	{
		$db_maildata = queryrunpre("SELECT * FROM ".$dbtable." WHERE `mail` ='".$_SESSION["mail"]."'",null);
		if(empty($db_maildata)){}
		else{
			$Err.='<div class="err">※使用済みのメールアドレスです</div>';
		}
	}

	if(SeChk('pass')=='')
		$Err.='<div class="err">※パスワードを入力してください</div>';

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

	//メール送信
	$usemail=str_replace($SearchKey,$SearchValue,file_get_contents($MAIL_DAT));

	//ip追加
	$usemail=str_replace('{{ip}}',$_SERVER['REMOTE_ADDR'],$usemail);
	$usemail=preg_replace("/{{.*?}}/","",$usemail);

	mb_internal_encoding("UTF-8") ;
	mb_send_mail($ownermail,$mailsub,htmlspecialchars_decode($usemail));

	newdata($_SESSION["name"],$_SESSION["mail"],md5($_SESSION["pass"].$_SESSION["mail"]),date('Y/m/d H:i:s'),$_SERVER['REMOTE_ADDR']);
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

echo $usehtml;

//issetがメンチなので関数つくったった。
function SeChk($sessionName)
{
	return isset($_SESSION[$sessionName]) ? $_SESSION[$sessionName] : '';
}
?>