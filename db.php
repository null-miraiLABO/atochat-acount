<?php
//設定部
$user ="db_mizukinet";
$password = "4CBEpHSn";
$dbname = "db_mizukinet_1";
$dbtable = "acount";

//データベース初期化部
$dsn  = "mysql:host=localhost;charset=utf8;dbname=".$dbname;
$db = new PDO($dsn,$user,$password);

//mysql文を使ってデータを得る
function queryrunpre($query,$param)
{
        global $db;
        $pre = $db->prepare($query);
        if($pre->execute($param))
                return $pre->fetchAll();
        else
                return false;
}

//新しいデータを作る関数
function newdata($name,$mail,$pass,$time,$ip)
{
        global $db,$dbtable;
        $insert_query = "INSERT INTO ".$dbtable." (name,mail,pass,time,ip) ".
          "VALUES(".$db->quote($name).",".$db->quote($mail).",".$db->quote($pass).",".$db->quote($time).",".$db->quote($ip).")";

        queryrunpre($insert_query,null);
}

?>