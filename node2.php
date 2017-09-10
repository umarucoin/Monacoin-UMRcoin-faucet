<?php
if(phpversion()>="4.1.0"){
extract($_POST);
extract($_GET);
extract($_SERVER);
extract($_COOKIE);
}
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); //Notice&Warning消し

//準備物：easybitcoin.php、time.txt、nodes.txt、log.txt、その他monacoind、umrcoind等サーバー類
//編集ポイント27行目:id,pass,ip,port、33行目:reCAPTCHA data-sitekey、104行目:reCAPTCHA privatekey
//111行目116行目:id,pass,ip,port、

function index(){
	
	$timefile = "time.txt";
	$fp5 = fopen($timefile,"r");
	flock($fp5, 2);
	rewind($fp5);
	$lasttime=fread($fp5,filesize($timefile));
	fclose($fp5);
	

  require_once 'easybitcoin.php';//easybitcoin.php
  $zandaka = 0;

  $monacoin = new Bitcoin('user','password','127.0.0.1','9402');//monacoind rpc
  $zandaka = $monacoin->getbalance("",6);

	echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html\; charset=UTF-8\"><title>運命の分かれ道 faucet</title><script type=\"text/javascript\" src=\"https://www.google.com/recaptcha/api.js?hl=ja\"></script></head><body bgcolor=\"#efefef\" text=\"black\" link=\"blue\" alink=\"red\" vlink=\"#660099\">";
	echo "<FONT SIZE=+1 COLOR=\"#FF0000\">UMRcoin / Monacoin 疑似乱数 faucet</FONT><br><br><dl><dt>1 ：<font color=green><b>UMRcoin / Monacoin 疑似乱数 faucetの使い方</B></font> ：<b>".$lasttime." </b>ID:faucet<dd><br>下記欄内にそれぞれの<b>Wallet address</b>を記入しボタンを押してください。<br>UMRcoinと場合によってはMonacoinがもらえます。<br>現在Monacoinがもらえる確率はおよそ10%です。<br><br>ただし現在の貯水槽残高：<b>".$zandaka."</b> MONAがなくなればMonacoinはもらえません。<br>Monacoin貯水槽address：MK2oKtPFdcNkwrbieYLjdC3GaebAtYKkwc<br><br>";
	echo "＊＊＊注意事項＊＊＊<br><font color=\"red\">UMRcoin Core（umrcoin-qtかumrcoind）をUPnP設定を有効にして起動し、<br>最低1ノード以上に接続した状態（ポート9441内向き）で待ち受けしておく必要があります。</font><br>firewallやルーターが優秀過ぎる場合は上記設定でも使用できないことがあります。<br>UMRcoin公式サイトへのリンクはこのページの左下にあります。<br><br>";
	echo "<form method=\"POST\" action=\"./node2.php\">UMRcoin address to get 0.01 UMR <font color=\"red\">（必須）</font><br><input type=\"text\" name=\"kiji\" size=\"80\"><input type=\"submit\" value=\"登録\"><br>Monacoin address to get 0.01 MONA （確率10%）<font color=\"red\">（必須）</font><br><input type=\"text\" name=\"kiji2\" size=\"80\"><input type=\"hidden\" name=\"mode\" value=\"regist\"><br><div class=\"g-recaptcha\" data-callback=\"clearcall\" data-sitekey=\"6aaaa\"></div></form>";
	echo "<FONT SIZE=-1><br><b>＜最新25faucet（上の方が新しい）＞</b> <a href=\"./log.txt\">log</a></b><br><br>";

  $datfile = "nodes.txt";
  $fp3 = fopen($datfile,"r");
  flock($fp3, 2);
  rewind($fp3);
  $buf=fread($fp3,filesize($datfile));
  $line2 = explode("\n",$buf);
  fclose($fp3);
  $countline=count($line2);
  for($j = 0;$j <= 24;$j++){
  	  echo $line2[$j]."<br>";
  }
  
  die("</font></dl><br><font size=1><!-- <a href=\"./node2.php?\">UMRcoin faucet (2017/09/01)</a> --> <a href=\"https://umarucoin.github.io/\" target=\"_blank\">UMRcoin公式サイト</font></body></html>");
  exit;
}

function error($mes){
  echo "<html><head><title>error!</title></head><body><br><br><hr size=1><br><br>
        <center><font color=red size=4><b>$mes<br></b></font></center>
        <br><br><hr size=1>";
  die("</body></html>");
  exit;
}

function error2($mes){
  echo "<html><head><title>error!</title></head><body>";
  echo "<br><br><hr size=1><br><br>
        <center><font color=red size=4><b>$mes<br></b></font></center>
        <br><br><hr size=1>";
  die("</body></html>");
  exit;
}

function  proxy_connect($port) {
  $fp = fsockopen (getenv("REMOTE_ADDR"), $port,$a,$b,2);
  if(!$fp){return 0;}else{return 1;}
}

function checkrecaptcha($recaptcha_response, $privatekey) {
	if(isset($recaptcha_response)){
		$code = $recaptcha_response;
	} else {
		$code = "";
	}
	$endpoint = "https://www.google.com/recaptcha/api/siteverify?secret={$privatekey}&response={$code}";//http://neoblog.itniti.net/recaptcha-1/
	$json = @file_get_contents($endpoint);
	$result = json_decode($json);
	if($result->success){
		return true;
	} else {
		return false;
	}
}

function regist($kiji){

    if(proxy_connect('9441') != 1){
      error("ＥＲＲＯＲ！　PORT(9441) NOT OPEN / CHECK UMRCOIN UPNP ON");
    }

	if(isset($_POST["g-recaptcha-response"])){
		$recaptcha = $_POST["g-recaptcha-response"];
	} else { $recaptcha = ""; }

	if(isset($_POST["kiji2"])){
		$kiji2 = $_POST["kiji2"];
	} else { $kiji2 = ""; }

	$privatekey = "6AAAAA";//priv_key

  require_once 'easybitcoin.php';//

	if(checkrecaptcha("$recaptcha", "$privatekey")){


  $bitcoin = new Bitcoin('user','password','127.0.0.1','9442');//umrcoind rpc
  $validateaddr = 0;
  $existflag = 0;
  $ransu = 0;

  $monacoin = new Bitcoin('user','password','127.0.0.1','9402');//atari monacoind rpc
  $validateaddr2 = 0;
  $existflag2 = 0;

  $ransu = mt_rand(0,99);
  $ransukekka = "";

  $kiji = trim($kiji);
  $kiji2 = trim($kiji2);
  if ((strlen($kiji) > 0) && (strlen($kiji2) > 0)) {
    	  $validateaddr = $bitcoin->validateaddress("$kiji");
  	  $existflag = $validateaddr[isvalid];
    	  $validateaddr2 = $monacoin->validateaddress("$kiji2");
  	  $existflag2 = $validateaddr2[isvalid];
    	  if (($existflag == 1) && ($existflag2 == 1)) {
  $datfile = "nodes.txt";
  $fp1 = fopen($datfile,"r");
  flock($fp1, 2);
  rewind($fp1);
  $contents1 = fread ($fp1, filesize($datfile));
  fclose($fp1);
  if(fnmatch("*$kiji2*",$contents1)){error("そのMonacoin addressは最近25回以内に投稿されています");}
  
  if($ransu > 90){
    $ransukekka = "ATARI!!!";
  } else { $ransukekka = "HAZURE..."; }

  $time = time();
  $now = "(".gmdate("y/m/d",$time+9*60*60)." ".gmdate("H:i:s",$time+9*60*60).")";
  $ip = getenv("REMOTE_ADDR");
  $logfile = "log.txt";
  $fp0 = fopen($logfile,"r");
  flock($fp0, 2);
  rewind($fp0);
  $contents = fread ($fp0, filesize($logfile));
  fclose($fp0);
  $fp0 = fopen($logfile,"w");
  flock($fp0, 2);
  rewind($fp0);
  $line1 = explode("\n",$contents);
  $countline1=count($line1);
  fputs($fp0, "$kiji\t$kiji2\t$now\t$ip\t$ransukekka\n");
  for($i = 0;$i < $countline1-2;$i++){
  fputs($fp0, "$line1[$i]\n");
  }
  //fputs($fp0, "$contents");
  fclose($fp0);
  
  $timefile = "time.txt";
  $fp4 = fopen($timefile,"w");
  flock($fp4, 2);
  rewind($fp4);
  fputs($fp4, "$now");
  fclose($fp4);

  $datfile = "nodes.txt";
  $fp2 = fopen($datfile,"r");
  flock($fp2, 2);
  rewind($fp2);
  $contents2 = fread ($fp2, filesize($datfile));
  fclose($fp2);
  $fp2 = fopen($datfile,"w");
  flock($fp2, 2);
  rewind($fp2);
  $line2 = explode("\n",$contents2);
  $countline=count($line2);
  fputs($fp2, "$kiji2\n");
  for($j = 0;$j < $countline-2;$j++){
  fputs($fp2, "$line2[$j]\n");
  }
  //fputs($fp2, "$contents2");
  fclose($fp2);

  if($ransu > 90){ $sendmona = $monacoin->sendtoaddress("$kiji2",0.01); }
  $send = $bitcoin->sendtoaddress("$kiji",0.01);
//
    	  } else {error("投稿できませんでした。validate_error（".getenv("REMOTE_ADDR")."）");}
  } else {error("投稿できませんでした。（".getenv("REMOTE_ADDR")."）");}
  } else {error("reCAPTCHA失敗。（".getenv("REMOTE_ADDR")."）");}
  
  


echo '<html><head>
<title>登録</title></head>
<body bgcolor="#efefef" text="black" link="blue" alink="red" vlink="#660099"><!-- 
';

echo ' --><font size="+1" color="red">登録完了</font><br>
<font size="-1"><dl><dt><dd>
';

$ooatari = "Monacoin...hazure...<br><br><br>";
if ($ransu > 90){ $ooatari = "おめでとうございます！Monacoinも送金されました。<br> 0.01 MONA to ".$kiji2."<br> TXID:".$sendmona."<br><br><br>"; }

  echo $ooatari;
  echo "<!-- ".$ransu." -->";
  echo $kiji;
  echo "<br><br>登録ありがとうございました。</font></dl><br>UMRcoin TXID:".$send."<br><font size=1><a href=\"./node2.php?\">return</a></font></body></html>";
  exit;
}
/*-----------Main-------------*/
switch($mode){
  case 'regist':
    regist($kiji);
    break;
    default:
    	if(!$kiji) {index();
    	}else{
    	error2("unknown error");}
}
?>
