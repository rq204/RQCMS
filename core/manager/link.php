<?php
if(RQ_POST)
{
	if($action == 'addlink') 
	{
		$name    = trim($_POST['name']);
		$url     = trim($_POST['url']);
		$note    = trim($_POST['note']);
		$bak    = trim($_POST['bak']);
		$visible = intval($_POST['visible']);
		$result  = checksitename($name);
		$result .= checkurl($url,0);
		$result .= checknote($note);
		if($result) 
		{
			redirect($result);
		}
		$name    = char_cv($name);
		$url     = char_cv($url);
		$note    = char_cv($note);
		$rs = $DB->fetch_first("SELECT count(*) AS links FROM {$dbprefix}link WHERE name='$name' AND url='$url'  ");
		if($rs['links'])
		{
			redirect('该链接在数据库中已存在', $admin_url.'?file=link');
		}
		$DB->query("INSERT INTO {$dbprefix}link (name, url, note, visible,bak) VALUES ('$name', '$url', '$note' ,'$visible','$bak')");
		setting_recache();
		redirect('添加链接成功', $admin_url.'?file=link');
	}
	else if($action=='domorelink')
	{
		if(isset($_POST['delete'])&&$ids = implode_ids($_POST['delete']))
		{
			$DB->query("DELETE FROM	{$dbprefix}link WHERE lid IN ($ids)  ");
		}
		if(is_array($_POST['name'])) 
		{
			foreach($_POST['name'] as $linkid => $value) 
			{
				$DB->query("UPDATE {$dbprefix}link SET displayorder='".intval($_POST['displayorder'][$linkid])."', name='".char_cv(trim($_POST['name'][$linkid]))."', url='".char_cv(trim($_POST['url'][$linkid]))."', note='".char_cv(trim($_POST['note'][$linkid]))."', visible='".intval($_POST['visible'][$linkid])."',bak='".$_POST['bak'][$linkid]."' WHERE lid='".intval($linkid)."'  ");
			}
		}
		setting_recache();
		redirect('链接已成功更新', $admin_url.'?file=link');
	}
}

if(!$action) $action = 'list';


// 检查链接名字是否符合逻辑
function checksitename($sitename) {
	if(!$sitename || strlen($sitename) > 30) {
		$result = '站点名不能空并不能大于30个字符<br />';
		return $result;
	}
	elseif(preg_match("[<>{}(),%#|^&!`$]",$sitename)) {
		$result = '站点名中不能含有特殊字符<br />';
		return $result;
	}
}

// 检查链接描述是否符合逻辑
function checknote($note = '') 
{
	if($note && strlen($note) > 200) 
	{
		$result = '站点描述不能大于200个字符<br />';
		return $result;
	}
	
}
if($action == 'add') $subnav = '添加链接';
if ($action == 'list')
 {
	$query = $DB->query("SELECT * FROM {$dbprefix}link ORDER BY displayorder");
	$linkdb = array();
	while ($link = $DB->fetch_array($query))
	{
		if ($link['visible'] == '1') {
			$link['visible'] = '<option value="1" selected>显示</option><option value="0">隐藏</option>';
		} else {
			$link['visible'] = '<option value="1">显示</option><option value="0" selected>隐藏</option>';
		}
		$linkdb[] = $link;
	}
	unset($link);
	$DB->free_result($query);
	$subnav = '编辑链接';
}
