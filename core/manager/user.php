<?php
if(empty($action)) $action = 'list';
$groupdb=array(1=>'管理员',2=>'编辑',3=>'注册会员');
$readonly='';

if(RQ_POST)
{
	// //添加用户
	if($action == 'adduser'||$action == 'moduser')
	{
		$username       = trim($_POST['username']);
		$newpassword    = trim($_POST['newpassword']);
		$comfirpassword = trim($_POST['comfirpassword']);
		$showgid        = intval($_POST['groupid']);
		$userid=isset($_POST['userid'])?intval($_POST['userid']):'';
		$email =$_POST['email'];

		if (!$username || strlen($username) > 20) {
			redirect('登陆名不能为空并且不能超过20个字符');
		}
		$name_key = array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#','$','(',')','%','@','+','?',';','^');
		foreach($name_key as $value){
			if (strpos($username,$value) !== false){
				redirect('用户名包含敏感字符');
			}
		}
		if ($newpassword != $comfirpassword) {
			redirect('请确认输入的密码一致');
		}
		
		if($action == 'moduser'&&!empty($newpassword)){
			if (strpos($newpassword,"\n") !== false || strpos($newpassword,"\r") !== false || strpos($newpassword,"\t") !== false) {
				redirect('密码包含不可接受字符.');
			}
		}
		$url = char_cv($url);
		
		$sqladd=$action == 'moduser'?' and `uid`!='.$userid:'';

		if ($email)
		{
			$r = $DB->fetch_first("SELECT uid FROM {$dbprefix}user WHERE email='$email' $sqladd");
			if($r['uid']) {
				redirect('该E-mail已被注册');
			}
		}
		
		if($action == 'adduser')
		{
			$newpassword = md5(md5($newpassword));
			$query = $DB->query("SELECT uid FROM {$dbprefix}user WHERE username='$username'");
			if($DB->num_rows($query)) redirect('该用户名已被注册');

			$DB->query("INSERT INTO {$dbprefix}user (username, password, email, regdateline, regip, groupid) VALUES ('$username', '$newpassword', '$email', '$timestamp', '$onlineip', '$showgid')");
			redirect('添加新用户成功', $admin_url.'?file=user&action=list');
		}
		else if($action == 'moduser')
		{
			$sql="update {$dbprefix}user set `email`='$email'";
			if(!empty($newpassword)) $sql.=",`password`='".md5(md5($newpassword))."'";
			$sql.="where uid='$userid'";
			$DB->query($sql);
			redirect('用户编辑成功', $admin_url.'?file=user&action=mod&userid='.$userid);
		}
	}

	if($action=='del'||$action=='delusers')
	{
		if(empty($_POST['user'])) redirect('请先选择要删除的用户',$admin_url.'?file=user');
		$deluids=implode_ids($_POST['user']);
		$query = $DB->query("SELECT * FROM {$dbprefix}user where uid in (".$deluids.")$sqladd");
		$userdb=array();
		$delusername='';
		while ($user = $DB->fetch_array($query))
		{
			$userdb[]=$user;
			$delusername.=$user['username'].',';
		}

		if($action=='delusers')
		{
			//删除用户
			$DB->query("Delete FROM {$dbprefix}user where `uid` in (".$deluids.")$sqladd");
			//删除文章和附件
			$aids=array();
			if ($_POST['deluserarticle'])
			{
				include RQ_CORE.'/include/attachment.php';
				$query = $DB->query("SELECT aid FROM `{$dbprefix}article` WHERE `userid` IN ($deluids)");
				while ($article = $DB->fetch_array($query)) {
					$aids[]=$article['aid'];
				}//end while
				$delaids=implode_ids($aids);
			}
			redirect('删除用户'.trim($delusername,',').'成功',$admin_url.'?file=user');
		}
	}
}
else
{
	$showgid        = isset($_GET['groupid'])?$_GET['groupid']:'';
	$groupselect[1]=$groupselect[2]=$groupselect[3]=$groupselect[4]='';
	if ($action == 'add')
	{
		$info['username']=$info['uid']=$info['email']=$info['msn']='';
		$nav='添加用户';
		$showgid=1;
		$do = 'adduser';
		$groupselect[1] = 'selected';
	} 
	elseif($action=='mod')
	{
		$nav='编辑用户';
		$userid = intval($_GET['userid']);
		$do = 'moduser';
		$info = $DB->fetch_first("SELECT * FROM {$dbprefix}user WHERE uid='$userid'");
		if($info['groupid']>=$groupid&&$info['username']!=$username) redirect('您无权编辑比自己权限大或同等权限的用户',$admin_url.'?file=user');
		$groupselect[$info['groupid']] = 'selected';
		$showgid=$info['groupid'];
		$readonly='readonly=“true"';
	}
	elseif($action == 'list') 
	{
		if($page) {
			$start_limit = ($page - 1) * 30;
		} else {
			$start_limit = 0;
			$page = 1;
		}
		$sqladd = " WHERE 1 ";
		$pagelink = '';
		//察看是否发表过评论
		$lastpost = (!isset($_GET['lastpost']))?'':$_GET['lastpost'] ;
		if ($lastpost == 'already') {
			$sqladd .= " AND lastpost !=null";
			$pagelink .= '&lastpost=already';
			$subnav = '发表过评论的用户';
		}
		elseif ($lastpost == 'never') {
			$sqladd .= " AND lastpost='0'";
			$pagelink .= '&lastpost=never';
			$subnav = '从未发表过评论的用户';
		}

		//察看用户组
		if ($showgid && in_array($showgid,array_flip($groupdb))) {
			$sqladd .= " AND groupid='$showgid'";
			$pagelink .= '&groupid='.$showgid;
			$subnav = $groupdb[$showgid].'的用户';
		}
		//察看IP段
		$ip =isset($_GET['ip'])? char_cv($_GET['ip']):'';
		if ($ip)
		{
			$frontlen = strrpos($ip, '.');
			$ipc = substr($ip, 0, $frontlen);
			$sqladd .= " AND (loginip LIKE '%".$ipc."%')";
			$pagelink .= '&ip='.$ip;
			$subnav  = '上次登陆IP为['.$ip.']同一C段的相关用户';
		}
		//搜索用户
		$srhname =isset($_GET['srhname'])?( char_cv($_GET['srhname'] ? $_GET['srhname'] : $_POST['srhname'])):'';
		if ($srhname) {
			$sqladd .= " AND (BINARY username LIKE '%".str_replace('_', '\_', $srhname)."%' OR username='$srhname')";
			$pagelink .= '&srhname='.$srhname;
		}

		//排序
		$order =isset($_GET['order'])? $_GET['order']:'';
		if ($order && in_array($order,array('username','logincount','regdateline'))) {
			$orderby = $order;
			$orderdb = array('username'=>'用户名','logincount'=>'登陆次数','regdateline'=>'注册时间');
			$subnav = '以'.$orderdb[$order].'降序察看全部用户';
			$pagelink .= '&order='.$order;
		} else {
			$orderby = 'uid';
		}
		$total = $DB->fetch_first("SELECT count(uid) as ct FROM {$dbprefix}user ".$sqladd)['ct'];
		
		$multipage = multi($total, 30, $page, $admin_url.'?file=user&action=list'.$pagelink);
		$query = $DB->query("SELECT * FROM {$dbprefix}user $sqladd ORDER BY $orderby DESC LIMIT $start_limit, 30");
		$userdb = array();

		while ($user = $DB->fetch_array($query))
		{
			$user['lastpost']    = $user['lastpost'] ? $user['lastpost'] : '从未发表';
			$user['email']=$user['email']? '<a href="mailto:'.$user['email'].'" target="_blank">'.$user['email'].'</a>' : '<font color="#FF0000">Null</font>';
			$user['logintime'] = $user['logintime'] ? $user['logintime'] : '从未登陆';
			$user['loginip']   = $user['loginip'] ? $user['loginip'] : '从未登陆';
			$user['group'] = $groupdb[$user['groupid']];
			$user['disabled'] = $user['groupid'] =='0' ? 'disabled' : '';
			$userdb[] = $user;
		}
		unset($user);
		$DB->free_result($query);
	} //end list
}