<?php
  $uagent = $_SERVER['HTTP_USER_AGENT'];
  $safari = ((stripos($uagent, "chrome") === false) && (stripos($uagent, "safari") !== false))
?>
<html>
<head>
<link rel="stylesheet" href="/meats.css">
<title>Meat Prices from Grow and Behold</title>
<script src="https://code.highcharts.com/highcharts.src.js"></script>
<script>
  function updateDiv()
  {
    var name = document.getElementById("divName").value.replace("&", "&amp;").replace("\"", "&quot;").replace('"', "&quot;");
    if(names.includes(name))
      window.scroll({behavior: 'smooth', left: 0, top: document.getElementById("wrapper-" + names.indexOf(name)).getBoundingClientRect().top+window.scrollY-30});
  }
</script>
</head>
<body>
<div id="navbar">
<form onsubmit="updateDiv(); return false;">
  <input type="submit" style="height: 22px !important; top: 4px; position: absolute; left: 198px;" value="go">

  <?php if(!$safari){ ?>
  <input list="meats" name="meat" id="divName" style="height: 22px !important; top: 4px; position: absolute; left: 20px; width: 180px;">
    <datalist id="meats">
    <?php } else { ?><select id="divName" style="height: 22px !important; top: 4px; position: absolute; left: 20px; width: 180px;"><?php

        }

	$con = mysqli_connect("localhost", "meats", "d7bxqEGKtk", "meats");
	$symbols = mysqli_fetch_all(mysqli_query($con, "SELECT DISTINCT  `name` FROM growandbehold"), MYSQLI_NUM);

	for($i = 0; $i < count($symbols); $i++):
?>
	<option value="<?php echo str_replace('"', "&quot;", $symbols[$i][0]); ?>"><?php echo $symbols[$i][0]; ?></option>
<?php
	endfor;
  if($safari){ ?>
    </select> <?php } else { ?>
    </datalist>
<?php
  }
?>
</form>
</div>
<div style="height: 30px !important"></div>
<script>
var names = [<?php
	$names = [];
	foreach($symbols as $symbol)
		$names[] = '"' . str_replace('"', "&quot;", $symbol[0]) . '"';
	echo implode(", ", $names);
?>];
</script>
<?php
	for($i = 0; $i < count($symbols); $i++):

		$sql = 'SELECT AVG(`price`) FROM growandbehold WHERE `name`="' . mysqli_real_escape_string($con, $symbols[$i][0]) . '" AND `date` > CURDATE() - INTERVAL 60 day';
		$twoMonthAvg = mysqli_fetch_all(mysqli_query($con, $sql))[0][0];

		$sql =  "SELECT `link` FROM growandbehold WHERE `name`=\"" . mysqli_real_escape_string($con, $symbols[$i][0]) . '" ORDER BY `date` DESC LIMIT 1';
		$link = mysqli_fetch_all(mysqli_query($con, $sql))[0][0];
 ?>
<div class="symbol-wrapper" id="wrapper-<?php echo $i; ?>"></div>
<hr class="line"/>
<script>
Highcharts.chart('wrapper-<?php echo $i; ?>', {

  title: {
    text: '<?php echo str_replace("'", "&#39;", $symbols[$i][0]); ?> - Price History'
  },

  subtitle: {
    text: '<a href="<?php echo $link; ?>"><?php echo $link; ?></a>'
  },

  xAxis: {
    type: 'datetime',
    min: 1528934400000
  },

  yAxis: {
    title: {
      text: 'USD / <?php
		$sql =  'SELECT `unit` FROM growandbehold WHERE `name`="' . mysqli_real_escape_string($con, $symbols[$i][0]) . '" ORDER BY `date` DESC LIMIT 1';
		echo mysqli_fetch_all(mysqli_query($con, $sql), MYSQLI_NUM)[0][0]; ?>'
    },
    plotLines: [{
	value: <?php echo $twoMonthAvg; ?>,
	color: 'red',
	dashstyle: 'shortdash',
	width: 2,
	label: {
	  text: '2 Month Average: <?php echo $twoMonthAvg; ?>'
	}
    }]
  },

  plotOptions: {
    series: {
      label: {
        connectorAllowed: false
      }
    }
  },

  series: [{
    name: '<?php echo str_replace("'", "&#39;", $symbols[$i][0]); ?>',
    data: [<?php
	$points = [];
	$sql =  'SELECT `price`, `date` FROM growandbehold WHERE `name`="' . mysqli_real_escape_string($con, $symbols[$i][0]) . '" ORDER BY `date` ASC';
	//echo $sql;
	$pricesList = mysqli_fetch_all(mysqli_query($con, $sql), MYSQLI_NUM);
	foreach($pricesList as $point)
		$points[] = "[" . 1000*mktime(0, 0, 0, explode("-", $point[1])[1], explode("-", $point[1])[2], explode("-", $point[1])[0]) . ", " . $point[0] . "]";
	echo implode(", ", $points);
?>]
  },
  {
  name: 'Available (boolean)',
  data: [<?php

	$sql =  'SELECT `available`, `date` FROM growandbehold WHERE `name`="' . mysqli_real_escape_string($con, $symbols[$i][0]) . '" ORDER BY `date` ASC';
        $pricesList = mysqli_fetch_all(mysqli_query($con, $sql), MYSQLI_NUM);
	$avail = [];
	foreach($pricesList as $point)
		$avail[] = "[" . 1000*mktime(0, 0, 0, explode("-", $point[1])[1], explode("-", $point[1])[2], explode("-", $point[1])[0]) . ", " . (($point[0] == "yes") ? 1 : 0) . "]";
	echo implode(", ", $avail);
?>]}
   ]

});
</script>

<?php
	endfor;
?>

</body>
</html>
