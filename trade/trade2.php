<!DOCTYPE html>
<html>
<head>
  <title>バイナリーオプションデモ</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.min.js"></script>
  <script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
  <script type="text/javascript">
    setTimeout("location.reload()",5000);
  </script>
  <?php
  require_once "db.php";
  require_once __DIR__ . '/vendor/autoload.php';
// ここからインサート
  use Goutte\Client;
  $cli = new Client();
  $top = $cli->request('GET', 'https://info.finance.yahoo.co.jp/fx/detail/?code=USDJPY=FX');
  $bid=$top->filter('#USDJPY_detail_bid')->text();
  $ask=$top->filter('#USDJPY_detail_ask')->text();
  $median=($bid+$ask)/2;

  $time=date("Y-m-d H:i:s");

  $sql = "INSERT INTO yahoo_table2 VALUES (NULL,'{$time}','{$median}','{$bid}','{$ask}')";
  $stmt=$pdo->prepare($sql);
  $stmt->execute();

  // $sql = " DELETE FROM yahoo_table2 LIMIT 1";
  // $stmt=$pdo->prepare($sql);
  // $stmt->execute();

//予測 macd
$sql = "SELECT * FROM yahoo_table2 ORDER BY yahoo_id2 DESC LIMIT 36";
$stmt=$pdo->prepare($sql);
$stmt->execute();
$result=$stmt->fetchAll(PDO::FETCH_ASSOC);
//長期26日EMA＝前日のSMA+{2÷(n+1)}(当日終値－前日のSMA)
for ($i=0; $i < 9; $i++) {
//前日のSMA
for ($j=$i+1; $j < $i+27; $j++) {
  $SM1[$i]+=$result[$j]['bid'];
}
$SMA1[$i]=$SM1[$i]/26;
//当日終値
$bid_now1[$i]=$result[$i]['bid'];
//2÷(n+1)
$a1=2/27;
$longEMA[$i]=$a1*($bid_now1[$i]-$SMA1[$i])+$SMA1[$i];
}


//短期12日EMI
for ($i=0; $i < 9; $i++) {
//前日のSMA
for ($h=$i+1; $h <$i+13 ; $h++) {
  $SM2[$i]+=$result[$h]['bid'];
}
$SMA2[$i]=$SM2[$i]/12;
//当日終値
$bid_now2[$i]=$result[$i]['bid'];
//2÷(n+1)
$a2=2/13;
$shortEMA[$i]=$a2*($bid_now2[$i]-$SMA2[$i])+$SMA2[$i];
}


//MACD＝短期EMA-長期EMA
for ($i=0; $i < 9; $i++) {
$MACD[$i]=$shortEMA[$i]-$longEMA[$i];
$sig_sam+=$MACD[$i];
}
$macd=$MACD[8];


//シグナル＝MACDの指数平滑移動平均線
$sig=$sig_sam/9;

//MACDヒストグラム
$hist=$macd-$sig;

//余裕持たせたい
if ($hist>1) {
  $pre= "hi";
}elseif($hist<-1){
  $pre= "low";
} else {
  $pre= "待機が得策";
}

  ?>
</head>
<body>
<!-- チャート -->
<canvas id="myChart"></canvas>
<script type="text/javascript">
var ctx = document.getElementById('myChart').getContext('2d');
var myChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: [<?
$sql = "SELECT * FROM yahoo_table2 ORDER BY yahoo_id2 DESC LIMIT 10";
$stmt=$pdo->prepare($sql);
$stmt->execute();
$result=$stmt->fetchAll(PDO::FETCH_ASSOC);

for ($i=9; $i>=0 ; $i--) {
   echo "'".$result[$i]['time']."',";
}
;?>'','',''],
    datasets: [{
      label: '為替レート',
      data: [<?
for ($i=9; $i>=0 ; $i--) {
   echo "'".$result[$i]['median']."',";
}
$price=$result[0]['median'];
$price_id=$result[0]['yahoo_id2'];
;?>],
      backgroundColor: "rgba(153,255,51,0.4)"
    }]
  }
});
</script>
<div>
<?
$price_get1=$_GET["price"];
$price_id_get=$_GET["price_id"];
$price_id_get_add=$price_id_get+2;
$time_get=$_GET["time"];
$time_stp=strtotime($time_get);
$time_add1=strtotime("+10 second",$time_stp);
$time_add2= date('Y-m-d H:i:s',$time_add1);

$sql = "SELECT * FROM yahoo_table2 WHERE yahoo_id2 = $price_id_get_add";
$stmt=$pdo->prepare($sql);
$stmt->execute();
$result=$stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
  $id=$row['yahoo_id2'];
  $price_out=$row["median"];
}

if (isset($time_get)) {
  $time_add=$time_add2;
  $price_get=$price_get1;
} else {
  $time_add="";
  $price_get="";
}


$hl=$_GET["hl"];
  switch ($hl) {
    case 'high':
       $checked_high="checked";
      break;
    case 'low':
       $checked_low="checked";
      break;
    default:
      $checked_high="";
      $checked_low="";
      break;
  }

if(!empty($price_out)){
        if ($price_out>$price_get&&$hl=="high") {
        $cons= "成功 5000円➡10000円";
      }elseif ($price_out<$price_get&&$hl=="low") {
        $cons= "成功 5000円➡10000円";
      } else {
        $cons= "失敗 5000円➡0円";
      }
}else{
  $cons="";
}
?>
  <form >
    <input type="radio"  value="high" name="hl" required="" <?echo$checked_high;?>>high
    <input type="radio"  value="low" name="hl" required=""<?echo$checked_low;?>>low
    <input type="hidden"  value="<?echo $price ;?>" name="price">
    <input type="hidden"  value="<?echo $price_id ;?>" name="price_id">
    <input type="hidden"  value="<?echo $time ;?>" name="time">
    <input type="submit"  value="今すぐデモ">
  </form>
  <br>ペイアウト  : 2.00
  <br>購入  : 5000円
  <br>取引時間  :  １０秒後
  <br>取引原資産  : USD/JPY<br>

  <br>現在の時間 : <?echo $time ;?><br>
  <br>取引時間 : <?echo $time_get ;?>
  <br>判定時間 : <?echo $time_add;?> （10秒後）
  <br>取引時レート  :  <?echo $price_get;?>
  <br>判定時レート  :  <?echo $price_out;?>
  <br>結果  :  <?echo $cons;?><br>

  <br>予測  :  <?echo $pre;?><br>
<?echo "MACDヒストグラム：".$hist;?>
</div>
</body>
</html>