<?php
/*
<Secret Disk>
Copyright (C) 2012-2019 Secret <https://gdsecret.com>

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

if(isset($_POST['site_name'])){
	if(isset($_POST['register'])){
		$register=1;
	}else{
		$register=0;
	}
	
	$config='../config.php';
	$config_sample='../config-sample.php';
	$put_config = vsprintf(file_get_contents($config_sample),array(
		addslashes($_POST['site_name']),
		$register,
		implode('","',explode(',',$_POST['upload_banext'])),
		abs($_POST['upload_max_size']),
		abs($_POST['default_file_space'])
	));
	file_put_contents($config,$put_config);
	$_GET['success']=true;
	require('../config.php');
}

$view = new View('theme/admin_default.html','admin/nav.php','',$disk['site_name'],'系統設定',true);
?>
<?php if(isset($_GET['success'])){?>
	<div class="alert alert-success">編輯成功！</div>
<?php } ?>
<h2 class="page-header">系統設定</h2>
<form class="form-horizontal" method="post" action="editconfig.php">
	<fieldset>
		<legend>主要</legend>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="site_name">網站名稱：</label>
			<div class="col-sm-6">
				<input class="form-control" name="site_name" type="text" value="<?php echo $disk['site_name']; ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="register">開啟註冊：</label>
			<div class="col-sm-6">
				<label class="checkbox-inline">
					<input name="register" type="checkbox" value="1"<?php if($disk['register']){echo ' checked="checked"';} ?>>開啟
				</label>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>檔案</legend>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="upload_banext">禁止格式：</label>
			<div class="col-sm-6">
				<input class="form-control" name="upload_banext" type="text" value="<?php echo implode(',',$disk['upload']['banext']); ?>">
			</div>
			<div class="col-sm-4 help-block">
				禁止上傳的檔案格式，用『,』分割
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="upload_max_size">大小限制：</label>
			<div class="col-sm-6">
				<div class="input-group">
					<input class="form-control" name="upload_max_size" type="number" min="1" value="<?php echo $disk['upload']['max_size']; ?>">
					<span class="input-group-addon">KB</span>
				</div>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>預設</legend>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="default_file_space">儲存空間：</label>
			<div class="col-sm-6">
				<div class="input-group">
					<input class="form-control" name="default_file_space" type="number" min="1" value="<?php echo $disk['default']['file_space']; ?>">
					<span class="input-group-addon">KB</span>
				</div>
			</div>
		</div>
	</fieldset>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-6">
			<input class="btn btn-success btn-lg" type="submit" value="修改">
		</div>
	</div>
</form>
<?php
$view->render();
?>