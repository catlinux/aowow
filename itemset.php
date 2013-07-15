<?php
require_once('includes/allitems.php');
require_once('includes/allspells.php');
require_once('includes/allcomments.php');
require_once('includes/allscreenshots.php');
require_once('includes/allreputation.php');
$smarty->config_load($conf_file, 'itemset');

$id = intval($podrazdel);

$cache_key = cache_key($id);

if(!$itemset = load_cache(ITEMSET_PAGE, $cache_key))
{
	unset($itemset);

	$row = $DB->selectRow("SELECT * FROM ?_itemset WHERE itemsetID=? LIMIT 1", $id);
	if($row)
	{
		$itemset = array();
		$itemset['entry'] = $row['itemsetID'];
		$itemset['name'] = $row['name_loc'.$_SESSION['locale']];
		$itemset['minlevel'] = 255;
		$itemset['maxlevel'] = 0;
		$itemset['count'] = 0;
		$x = 0;
		$itemset['pieces'] = array();
		for($j=1;$j<=10;$j++)
		{
			if($row['item'.$j])
			{
				$itemset['pieces'][$itemset['count']] = array();
				$itemset['pieces'][$itemset['count']] = iteminfo($row['item'.$j]);

				if($itemset['pieces'][$itemset['count']]['level'] < $itemset['minlevel'])
					$itemset['minlevel'] = $itemset['pieces'][$itemset['count']]['level'];

				if($itemset['pieces'][$itemset['count']]['level'] > $itemset['maxlevel'])
					$itemset['maxlevel'] = $itemset['pieces'][$itemset['count']]['level'];

				$itemset['count']++;
			}
		}
		$rclass = array(1 => '1', 2 => '2', 4 => '3', 8 => '4', 16 => '5', 32 => '6', 64 => '7', 128 => '8', 256 => '9', 1024 => '11');

        if ($itemset['pieces'][$itemset['count']] > 0){

        $itemset['class'] = 'Hi';

        }
		$itemset['spells'] = array();
		for($j=1;$j<=8;$j++)
			if($row['spell'.$j])
			{
				$itemset['spells'][$x] = array();
				$itemset['spells'][$x]['entry'] = $row['spell'.$j];
				$itemset['spells'][$x]['tooltip'] = spell_desc($row['spell'.$j]);
				$itemset['spells'][$x]['bonus'] = $row['bonus'.$j];
				$x++;
			}
		for($i=0;$i<=$x-1;$i++)
			for($j=$i;$j<=$x-1;$j++)
				if($itemset['spells'][$j]['bonus'] < $itemset['spells'][$i]['bonus'])
				{
					UnSet($tmp);
					$tmp = $itemset['spells'][$i];
					$itemset['spells'][$i] = $itemset['spells'][$j];
					$itemset['spells'][$j] = $tmp;
				}
	}
	save_cache(ITEMSET_PAGE, $cache_key, $itemset);
}
$smarty->assign('itemset', $itemset);

global $page;
$page = array(
	'Mapper' => false,
	'Book' => false,
	'Title' => $itemset['name'].' - '.$smarty->get_config_vars('Item_Sets'),
	'tab' => 0,
	'type' => 4,
	'typeid' => $itemset['entry'],
	'username' => $_SESSION['username'],
	'path' => '[0, 2]'
);
$smarty->assign('page', $page);

// Комментарии
$smarty->assign('comments', getcomments($page['type'], $page['typeid']));
$smarty->assign('screenshots', getscreenshots($page['type'], $page['typeid']));

// --Передаем данные шаблонизатору--
// Количество MySQL запросов
$smarty->assign('mysql', $DB->getStatistics());
$smarty->assign('reputation', getreputation($page['username']));
// Запускаем шаблонизатор
$smarty->display('itemset.tpl');
?>