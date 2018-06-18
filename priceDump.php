<HTML>
<HEAD>
<link rel="stylesheet" href="/priceDump.css">
<TITLE>Meat Prices from Grow and Behold</TITLE>
</HEAD>
<BODY>
<pre>
<?php
$con = mysqli_connect("localhost", "meats", "d7bxqEGKtk", "meats");
$sql = "SELECT * FROM growandbehold WHERE `date`=CURDATE()";
$results = mysqli_fetch_all(mysqli_query($con, $sql), MYSQLI_NUM);
if(count($results) == 0)
{
	$sql = "SELECT * FROM growandbehold WHERE `date`=CURDATE() - INTERVAL 1 day";
	$results = mysqli_fetch_all(mysqli_query($con, $sql), MYSQLI_NUM);
}
//print_r($results);
//exit();
echo "<table>";
?>
<tr>
<td><b>Name</b></td>
<td><b>Price</b></td>
<td><b>Quantity<b/></td>
<td><b>Avail.</b></td>
<td><b>Link<b/></td>
<td><b>Date<b/></td>
<?php
foreach ($results as $result)
{
	echo "<tr>";
	echo "<td>" . $result[0] . "</td>";
	echo "<td>" . $result[1] . "</td>";
	echo "<td>" . $result[2] . "</td>";
	echo "<td>" . $result[3] . "</td>";
	echo "<td><a href='" . $result[4] . "'>click here</a></td>";
	echo "<td>" . $result[5] . "</td>";
	echo "</tr>";
}
echo "</table>";
?>
</pre>
</BODY>
</HTML>
