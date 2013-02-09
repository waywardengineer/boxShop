<?php
$auth='auth';
include("include/common.php");
$html = new Template('templates/main.tpl', 'templates/supplies.tpl');
$html->createNav();
include("include/alarm.php");
if($user->logged_in){
	$welcome_msg = "Welcome, $user->username";
}
else {
	$html = new Template('templates/main.tpl');
	$welcome_msg="Hello, guest";
	$html->set('formLoginUser', $form->value("user"));
	$html->set('formLoginUserError', $form->error("user"));
	$html->set('formLoginPass', $form->value("pass"));
	$html->set('formLoginPassError', $form->error("pass"));
	$showsections[]='login';
}
if($user->isTrusted()){
	$resultsArray = array(array('categoryID', 'itemID', 'Category', 'Item', 'Average Cost', 'Total spent by everyone', 'Needed now?'));
	$query="SELECT categoryID, itemID, category, item, ROUND(AVG(cost/qty), 2) as avg, ROUND(SUM(cost), 2) as sum , needednow FROM supplies_items JOIN supplies_categories USING(categoryID) JOIN supplies_purchases USING(itemID) GROUP BY itemID ORDER BY needednow DESC, sum DESC";
	$result = $database->query($query);
	while ($row = @mysql_fetch_array($result, MYSQL_NUM)){
		$str = array_pop($row)?'Yes':'';
		array_push($row, $str);
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


//die();
$html->set('msg', $welcome_msg);
//echo htmlspecialchars($html->doOutput(array()));
echo $html->doOutput(array());

