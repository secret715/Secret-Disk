<?php
/*
<Secret Disk>
Copyright (C) 2012-2016 太陽部落格站長 Secret <http://gdsecret.com>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, version 3.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

Also add information on how to contact you by electronic and paper mail.

  If your software can interact with users remotely through a computer
network, you should also make sure that it provides a way for users to
get its source.  For example, if your program is a web application, its
interface could display a "Source" link that leads users to an archive
of the code.  There are many ways you could offer source, and different
solutions will be better for different programs; see section 13 for the
specific requirements.

  You should also get your employer (if you work as a programmer) or school,
if any, to sign a "copyright disclaimer" for the program, if necessary.
For more information on this, and how to apply and follow the GNU AGPL, see
<http://www.gnu.org/licenses/>.
*/

set_include_path('../include/');
$includepath = true;

require_once('../Connections/SQL.php');
require_once('../config.php');
require_once('view.php');

if(!isset($_SESSION['Disk_Username']) or $_SESSION['Disk_UserGroup'] != 9){
	header("Location: ../index.php");
	exit;
}

if(isset($_GET['edit']) && $_GET['edit'] != ''){
    $_member = sd_get_result("SELECT * FROM `member` WHERE `id` = '%d'",array(abs($_GET['edit'])));
	if(isset($_POST['email'])&& filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
		if($_POST['password'] == ''){
			$_password = $_member['row']['password'];
		}else {
			$_password = sd_password($_POST['password'], $_member['row']['username']);
		}
		$SQL->query("UPDATE `member` SET `password` = '%s', `email` = '%s',`file_space`='%s',`level`='%d' WHERE `id` = '%d'",array($_password,$_POST['email'],abs($_POST['file_space']*1000),$_POST['level'],$_GET['edit']));
		$_GET['success']=true;
		$_GET['edit']=$_member['row']['id'];
		$_member = sd_get_result("SELECT * FROM `member` WHERE `id` = '%d'",array(abs($_GET['edit'])));
	}
}else{
	$limit_row=30;
	if(isset($_GET['page'])){
		$limit_start = abs(intval(($_GET['page']-1)*$limit_row));
		$_member = sd_get_result("SELECT * FROM `member` ORDER BY `id` ASC LIMIT %d,%d",array($limit_start,$limit_row));
	} else {
		$limit_start=0;
		$_member = sd_get_result("SELECT * FROM `member` ORDER BY `id` ASC LIMIT %d,%d",array($limit_start,$limit_row));
	}
	
}

$_all_member=sd_get_result("SELECT COUNT(*) FROM `member`");

if(isset($_GET['del']) && $_GET['del'] != '' && implode('',$_all_member['row'])>1 && $_member['row']['id']!=$_SESSION['Disk_Id']) {
	$_member = sd_get_result("SELECT * FROM `member` WHERE `id` = '%d'",array(abs($_GET['del'])));
	
	$_file=sd_get_result("SELECT `server_name`,`size` FROM `file` WHERE `owner`='%d'",array($_member['row']['id']));
	
	if($_file['num_rows']>0){
		do{
			@unlink('../file/'.$_file['row']['server_name'].'.sdfile');
		}while($_file['row']=$_file['query']->fetch_assoc());
	}
	
	$SQL->query("DELETE FROM `dir` WHERE `owner` = '%d'",array($_member['row']['id']));
	$SQL->query("DELETE FROM `file` WHERE `owner` = '%d'",array($_member['row']['id']));
	
	if(isset($_member['row']['avatar'])){
		$SQL->query("DELETE FROM `chat` WHERE `author` = '%d'",array($_member['row']['id']));
		$SQL->query("DELETE FROM `forum` WHERE `author` = '%d'",array($_member['row']['id']));
		$SQL->query("DELETE FROM `forum_reply` WHERE `author` = '%d'",array($_member['row']['id']));
	}
	
	$SQL->query("DELETE FROM `member` WHERE `id` = '%d'",array($_member['row']['id']));
	
	header("Location: account.php?delok");
}

$view = new View('theme/admin_default.html','admin/nav.php','',$disk['site_name'],'帳號管理',true);
?>
<?php if(isset($_GET['success'])){?>
	<div class="alert alert-success">修改成功！</div>
<?php }elseif(isset($_GET['delok'])){ ?>
	<div class="alert alert-success">成功刪除此帳號！</div>
<?php } ?>
<h2 class="page-header">帳號管理</h2>
<?php if(isset($_GET['edit'])) { ?>
<script>
$(function(){
	$('input[name="authpassword"]').on('keyup', function(){
		var $_error_msg=$(this).parent().siblings('.help-block');
		if($(this).val()!=$('input[name="password"]').val()){
			$_error_msg.html('<span class="text-danger">密碼不一致</span>'); 
			$('input[type="submit"]').attr('disabled','disabled');
		}else{
			$_error_msg.html('');
			$('input[type="submit"]').attr('disabled',false);
		}
	}).parent().parent().append('<div class="col-sm-3 help-block"></div>');
});
</script>
<form class="form-horizontal form-sm" action="account.php?edit=<?php echo $_member['row']['id']; ?>" method="POST">
	<div class="form-group">
		<label class="col-sm-3 control-label">帳號：</label>
		<div class="col-sm-6">
			<p class="form-control-static"><?php echo $_member['row']['username']; ?></p>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="username">密碼：</label>
		<div class="col-sm-6">
			<input class="form-control" name="password" type="password" maxlength="30">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="authpassword">確認密碼：</label>
		<div class="col-sm-6">
			<input class="form-control" name="authpassword" type="password">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="email">* 電子信箱：</label>
		<div class="col-sm-6">
			<input class="form-control" name="email" type="email" maxlength="255" required="required" value="<?php echo $_member['row']['email']; ?>">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="file_space">* 儲存空間：</label>
		<div class="col-sm-6">
			<div class="input-group">
				<input class="form-control" name="file_space" type="number" min="1" max="1000000000000" value="<?php echo $_member['row']['file_space']/1000; ?>">
				<span class="input-group-addon">KB</span>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">已用空間：</label>
		<div class="col-sm-6">
			<p class="form-control-static"><?php echo sd_size($_member['row']['used_space'],3).' / '.sd_size($_member['row']['file_space'],3).' ('.round($_member['row']['used_space']/$_member['row']['file_space']*100,3).'%)'.sd_space_progress($_member); ?></p>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">權限：</label>
		<div class="col-sm-6">
			<select class="form-control" name="level">
			<?php foreach(sd_member_level_array() as $key=>$value){ ?>
				<option value="<?php echo $key; ?>" <?php if($_member['row']['level']==$key){ ?>selected="selected"<?php } ?>><?php echo $value; ?></option>
			<?php } ?>
			</select>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">註冊日期：</label>
		<div class="col-sm-6">
			<p class="form-control-static"><?php echo $_member['row']['joined']; ?></p>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">最後登入：</label>
		<div class="col-sm-6">
			<p class="form-control-static"><?php echo $_member['row']['last_login']; ?></p>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-6">
			<input class="btn btn-success" type="submit" value="確認修改">
			<a class="btn btn-default" href="account.php">取消</a>
		</div>
	</div>
</form>
<?php } else { ?>
<script>
$(function(){
	$('a.btn.btn-danger').click(function(e){
		if(!window.confirm("確定刪除此帳號？")){
			e.preventDefault();
		}
	});
});
</script>
<table class="table table-striped table-hover">
  <tr>
    <th width="10%">ID</th>
    <th width="20%">帳號</th>
	<th width="20%">電子信箱</th>
	<th width="20%">檔案空間</th>
    <th width="15%">權限</th>
    <th width="15%">管理</th>
  </tr>
<?php do { ?>
  <tr>
    <td><?php echo $_member['row']['id']; ?></td>
    <td><?php echo $_member['row']['username']; ?></td>
	<td><small><?php echo $_member['row']['email']; ?></small></td>
	<td><small><?php echo sd_size($_member['row']['used_space'],1).' / '.sd_size($_member['row']['file_space'],1); ?></small></td>
    <td><?php echo sd_member_level($_member['row']['level']); ?></td>
    <td>
		<a href="account.php?edit=<?php echo $_member['row']['id'] ;?>" class="btn btn-info btn-sm">編輯</a>
		<?php if(implode('',$_all_member['row'])>1 && $_member['row']['id']!=$_SESSION['Disk_Id']){ ?>
			<a href="account.php?del=<?php echo $_member['row']['id'] ;?>" class="btn btn-danger btn-sm">刪除</a>
		<?php } ?>
	</td>
  </tr>
<?php } while ($_member['row'] = $_member['query']->fetch_assoc()); ?>
</table>
<div>
<?php
echo sd_page_pagination('account.php',@$_GET['page'],implode('',$_all_member['row']),30);
?>
</div>
<?php } ?>
<?php
$view->render();
?>