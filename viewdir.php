<?php
/*
<Secret Disk>
Copyright (C) 2012-2017 太陽部落格站長 Secret <http://gdsecret.com>

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

require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/view.php');

if(!isset($_GET['id'])&&$_GET['id']==''){
	exit;
}

$_dir=sd_get_result("SELECT * FROM `dir` WHERE `share_id`='%s' AND `share` = '1'",array($_GET['id']));
if($_dir['num_rows']>0){
	$_DiskENV['dir']=$_dir['row']['id'];
}else{
	exit;
}

$_file = sd_get_result("SELECT * FROM `file` WHERE `dir`='%d' AND `share` = '1'",array($_DiskENV['dir']));
$_next_dir=sd_get_result("SELECT * FROM `dir` WHERE `parent`='%d' AND `share` = '1'",array($_DiskENV['dir']));

$_SESSION['Disk_Guest']=time();

$view = new View('include/theme/default.html','include/nav.php','',$disk['site_name'],'資料夾 '.$_dir['row']['name']);
?>
<h2 class="page-header"><?php echo $_dir['row']['name']; ?></h2>
<?php if($_next_dir['num_rows']>0 or $_file['num_rows']>0){ ?>
<table class="table table-striped table-hover">
	<tr>
		<th>名稱</th>
		<th>大小</th>
		<th>上傳時間</th>
		<th></th>
	</tr>
	<?php if($_next_dir['num_rows']>0){do{ ?>
	<tr>
		<td><a href="viewdir.php?id=<?php echo $_next_dir['row']['share_id']; ?>"><span class="glyphicon glyphicon-folder-close"></span> <?php echo $_next_dir['row']['name']; ?></a></td>
		<td></td>
		<td></td>
		<td></td>
	</tr>
	<?php }while ($_next_dir['row'] = $_next_dir['query']->fetch_assoc());} ?>
	<?php if($_file['num_rows']>0){do{ ?>
	<tr>
		<td><?php echo $_file['row']['name']; ?></td>
		<td><?php echo sd_size($_file['row']['size']); ?></td>
		<td><small><?php echo $_file['row']['mktime']; ?></small></td>
		<td><a class="btn btn-primary" href="download.php?id=<?php echo $_file['row']['share_id']; ?>"><span class="glyphicon glyphicon-floppy-disk"></span>下載</a></td>
	</tr>
	<?php
		}while ($_file['row'] = $_file['query']->fetch_assoc());
	}
	?>
</table>
<?php }else{ ?>
	<div class="alert alert-danger">沒有檔案</div>
<?php } ?>
<?php $view->render(); ?>