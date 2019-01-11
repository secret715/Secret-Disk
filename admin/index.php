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

if(isset($_GET['logout'])){
	sd_loginout();
	header("Location: ../login.php");
	exit;
}elseif(isset($_GET['clean'])){
	$_member_id_list = sd_get_result("SELECT `id` FROM `member`");
	if($_member_id_list['num_rows']>0){
		do{
			$_array[]=$_member_id_list['row']['id'];
		}while($_member_id_list['row']=$_member_id_list['query']->fetch_assoc());
	
		$_member_id=implode(',',$_array);
		
		$_file=sd_get_result("SELECT `server_name`,`size` FROM `file` WHERE `owner` NOT IN(%s)",array($_member_id));

		if($_file['num_rows']>0){
			do{
				@unlink('../file/'.$_file['row']['server_name'].'.sdfile');
			}while($_file['row']=$_file['query']->fetch_assoc());
		}
		
		$SQL->query("DELETE FROM `dir` WHERE `owner` NOT IN(%s)",array($_member_id));
		$SQL->query("DELETE FROM `file` WHERE `owner` NOT IN(%s)",array($_member_id));
		$_GET['success']=true;
	}
}
$view = new View('theme/admin_default.html','admin/nav.php','',$disk['site_name'],'系統管理',true);
?>
<?php if(isset($_GET['success'])){ ?>
	<div class="alert alert-success">刪除成功！</div>
<?php } ?>
<h2 class="page-header">系統管理</h2>
<p>歡迎來到系統管理介面！</p>
<div class="row">
	<div class="col-md-6">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title">系統</h3>
			</div>
			<div class="panel-body">
				目前版本：Secret Disk <?php echo sd_ver(); ?>&nbsp;&nbsp;<span id="ver_check"></span>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-warning">
			<div class="panel-heading">
				<h3 class="panel-title">帳號</h3>
			</div>
			<div class="panel-body">
				目前帳號數量：
				<?php echo implode('',$SQL->query("SELECT COUNT(*) FROM `member`")->fetch_assoc()); ?> 個
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-success">
			<div class="panel-heading">
				<h3 class="panel-title">檔案</h3>
			</div>
			<div class="panel-body">
				目前檔案數量：
				<?php echo implode('',$SQL->query("SELECT COUNT(*) FROM `file`")->fetch_assoc()); ?> 個&nbsp;&nbsp;<a href="?clean" class="btn btn-xs btn-danger">刪除多餘檔案</a>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-info">
			<div class="panel-heading">
				<h3 class="panel-title">空間</h3>
			</div>
			<div class="panel-body">
				目前已用空間：
				<?php echo sd_size(implode('',$SQL->query("SELECT SUM(`size`) FROM `file`")->fetch_assoc())); ?>
			</div>
		</div>
	</div>
</div>
<?php
$view->render();
?>