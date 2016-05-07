<?php
$authkey='boxshop94124';
include("include/common.php");
if (!$user->isTrusted()){
	header("Location: index.php");
	die();
}

$html->addSubTemplate('templates/supplieslog.tpl');
if($user->isTrusted()){
	$resultsArray = array(array('categoryID', 'itemID', 'UID', 'Category', 'Item', 'Quantity', 'Cost', 'Date', 'Bought by', 'Notes'));
	$query="SELECT categoryID, itemID, UID, category, item, qty, cost, FROM_UNIXTIME(supplies_purchases.timestamp, '%m.%d.%Y') AS datestr, username, notes FROM ((supplies_purchases JOIN supplies_items USING(itemID)) JOIN supplies_categories USING(categoryID)) JOIN users USING (UID) ORDER BY supplies_purchases.timestamp DESC";	
	$result = $database->query($query);
	while ($row = @mysql_fetch_array($result, MYSQL_NUM)){
		$resultsArray[] = $row;
	}
	$query = "SELECT categoryID, category FROM supplies_categories";
	$result = $database->query($query);
	$arr=array();
	while ($row = @mysql_fetch_array($result)){
		$arr[$row['categoryID']] = $row['category'];
	}
	$html->set('categoryOptions', $html->makeFormOptions($arr));
	$result = $database->query("SELECT categoryID FROM supplies_categories");
	$arr=array(0=>array());
	while ($row = @mysql_fetch_array($result)){
		$arr[$row['categoryID']] = array();
	}
	$query = "SELECT categoryID, itemID, item, COUNT(purchaseID) AS frequency FROM supplies_items JOIN supplies_purchases USING (itemID) GROUP BY itemID ORDER BY frequency DESC";
	$result = $database->query($query);
	while ($row = @mysql_fetch_array($result)){
		$arr[$row['categoryID']][$row['itemID']] = $row['item'];
	}
	$html->set('shitHead', json_encode($arr));
	$query = "SELECT UID, username FROM users WHERE UID = {$user->uid}
		UNION SELECT UID, username FROM users WHERE userlevel = 3 
		UNION SELECT UID, username FROM users WHERE userlevel = 9 
		UNION SELECT UID, username FROM users";
	$result = $database->query($query);
	$arr=array();
	while ($row = @mysql_fetch_array($result)){
		$arr[$row['UID']] = $row['username'];
	}
	$html->set('userOptions', $html->makeFormOptions($arr));
	$html->set('itemTable', $html->makeTable($resultsArray, 'Supply Items', true, false, 'itemTable'));
}
echo $html->doOutput(array());

