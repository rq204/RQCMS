<?php
print <<<EOT
<div class="mainbody">
  <table border="0"  cellspacing="0" cellpadding="0" style="width:100%;">
    <tr>
      <td valign="top" style="width:150px;"><div class="tableborder">
        <div class="tableheader">分类管理</div>
        <div class="leftmenubody">
          <div class="leftmenuitem">&#8226; <a href="{$admin_url}?file=category&action=add">添加分类</a></div>
          <div class="leftmenuitem">&#8226; <a href="{$admin_url}?file=category&action=list">分类管理</a></div>
        </div>
      </div>
      </div></td>
      <td valign="top" style="width:20px;"></td>
      <td valign="top">
	  <form action="{$admin_url}?file=category" method="POST"><table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
	  <tr><td class="rightmainbody"><table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
EOT;
if($action == 'list'){print <<<EOT
    <tr class="tdbheader">
      <td width="10%" nowrap>排序</td>
      <td width="25%">名称</td>
	  <td width="25%">友好网址</td>
      <td width="20%">文章数</td>
      <td width="20%" nowrap>操作</td>
    </tr>
EOT;
foreach($category as $cate)
{
	print <<<EOT
    <tr class="tablecell">
      <td nowrap><input class="formfield" style="text-align: center;font-size: 11px;" type="text" value="{$cate['displayorder']}" name="displayorder[{$cate['cid']}]" size="1"></td>
      <td><b>{$cate['name']}</b></td>
	  <td><b>{$cate['url']}</b></td>
      <td>{$cate['count']}</td>
      <td nowrap> <a href="{$admin_url}?file=article&action=add&cid={$cate['cid']}">添加文章</a> - <a href="{$admin_url}?file=category&action=mod&cid={$cate['cid']}">编辑</a> - <a href="{$admin_url}?file=category&action=del&cid={$cate['cid']}">删除</a></td>
    </tr>
EOT;
}

print <<<EOT
    <tr class="tablecell">
      <td colspan="5" align="center">
        <input type="hidden" name="action" value="updatedisplayorder">
        <input type="submit" value="更新排序" class="formbutton">
      </td>
    </tr>

EOT;
} elseif (in_array($action, array('add', 'mod'))){
print <<<EOT
    <input type="hidden" name="action" value="do{$action}">
    <input type="hidden" name="cid" value="$cate[cid]">
    <tr class="tdbheader">
      <td colspan="3">$subnav</td>
    </tr>
    <tr class="tablecell">
      <td>排序:</td>
      <td><input class="formfield" type="text" name="displayorder" size="4" maxlength="50" value="$cate[displayorder]"></td>
    </tr>
	<tr class="tablecell">
      <td>分类名称:</td>
      <td><input class="formfield" type="text" name="name" size="35" maxlength="50" value="$cate[name]"></td>
    </tr>
	<tr class="tablecell">
      <td>友好网址:</td>
      <td><input class="formfield" type="text" name="url" size="35" maxlength="50" value="$cate[url]"></td>
    </tr>
		<tr class="tablecell">
      <td>分类描述:</td>
      <td><input class="formfield" type="text" name="description" size="35" maxlength="50" value="$cate[description]"></td>
    </tr>
    <tr class="tablecell">
      <td colspan="2" align="center">
        <input type="submit" value="确定" class="formbutton">
      </td>
    </tr>

EOT;
} elseif ($action == 'del'){print <<<EOT
    <input type="hidden" name="cid" value="$cate[cid]">
    <input type="hidden" name="action" value="dodel">
    <tr class="alertheader">
      <td>$subnav</td>
    </tr>
    <tr class="alertbox">
      <td><p>您确定要删除【$cate[name]】分类吗?</p>
	  <p><b>本操作不可恢复，并会删除该分类中的所有文章及评论</b></p>
	  <p><input type="submit" value="确认" class="formbutton"></p>
	  </td>
    </tr>
EOT;
}
print <<<EOT
    <tr>
      <td class="tablebottom" colspan="5"></td>
    </tr>
      </table></td>
    </tr>
  </table>
</form></td>
    </tr>
  </table>
</div>
EOT;
