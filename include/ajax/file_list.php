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

set_include_path('../../include/');
$includepath = true;
require_once('../../Connections/SQL.php');
require_once('../../config.php');

if(!isset($_SESSION['Disk_Username']) or !isset($_GET[$_SESSION['Disk_Auth']])){
	exit;
}

$_DiskENV['dir']='0';
if(isset($_GET['dir'])&&$_GET['dir']>0){
	$_dir=sd_get_result("SELECT * FROM `dir` WHERE `id`='%d' AND `owner` = '%d'",array($_GET['dir'],$_SESSION['Disk_Id']));
	if($_dir['num_rows']>0){
		$_DiskENV['dir']=$_dir['row']['id'];
	}
}

if(!isset($_GET['sort'])){
	$_GET['sort']='00';
}
if(isset($_GET['sort'])){
	if(strlen($_GET['sort'])!=2){
		$_GET['sort']=str_pad($_GET['sort'],2,0,STR_PAD_LEFT);
	}
	$_table=array('name','size','mktime');
	$_a=str_split($_GET['sort'],1);
	if(!isset($_table[$_a[0]])){
		$_a[0]=0;
	}
	
	$_sort='`'.$_table[$_a[0]].'` ';
	
	if($_a[1]==1){
		$_sort.='DESC';
	}else{
		$_sort.='ASC';
	}
}

$limit_row=30;
if(isset($_GET['page'])&&$_GET['page']>0){
	$limit_start = abs(intval(($_GET['page']-1)*$limit_row));
	$_file = sd_get_result("SELECT * FROM `file` WHERE `dir`='%d' AND `owner`='%d' ORDER BY %s LIMIT %d,%d",array($_DiskENV['dir'],$_SESSION['Disk_Id'],$_sort,$limit_start,$limit_row));
} else {
	$limit_start=0;
	$_file = sd_get_result("SELECT * FROM `file` WHERE `dir`='%d' AND `owner`='%d' ORDER BY %s LIMIT %d,%d",array($_DiskENV['dir'],$_SESSION['Disk_Id'],$_sort,$limit_start,$limit_row));
}
?>
<ol class="breadcrumb">
	<li><a href="?dir=0">主目錄</a></li>
	<?php if($_DiskENV['dir']>0){echo sd_dir_path($_dir); ?>
	<?php if($_dir['row']['share']==1){ ?>
	<span class="glyphicon glyphicon-globe"></span>
	<?php } ?>
	<span class="dropdown pull-right">
		<span class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
			<span class="glyphicon glyphicon-cog"></span>
		</span>
		<ul class="dropdown-menu">
			<?php if($_dir['row']['share']==0){ ?>
			<li><a class="share" href="#"><span class="glyphicon glyphicon-globe"></span> 分享</a></li>
			<?php }else{ ?>
			<li><a href="viewdir.php?id=<?php echo $_dir['row']['share_id']; ?>" target="_black"><span class="glyphicon glyphicon-link"></span> 取得連結</a></li>
			<li><a class="unshare" href="#"><span class="glyphicon glyphicon-eye-close"></span> 取消分享</a></li>
			<?php } ?>
			<li><a class="rename" href="#" data-name="<?php echo $_dir['row']['name']; ?>"><span class="glyphicon glyphicon-pencil"></span> 重新命名</a></li>
			<li><a class="move" href="#"><span class="glyphicon glyphicon-move"></span> 移動</a></li>
			<li><a class="del" href="#"><span class="glyphicon glyphicon-trash"></span> 刪除</a></li>
		</ul>
	</span>
	<div class="clearfix"></div>
	<?php } ?>
</ol>
<?php if($_file['num_rows']>0){ ?>
<table class="table table-hover">
	<thead>
		<tr>
			<th></th>
			<th><a href="?dir=<?php echo $_DiskENV['dir']; ?>&sort=0<?php if($_a[0]==0)echo ($_a[1]+1)%2; else echo 0; ?>">名稱<?php if($_a[0]==0){ ?><span class="glyphicon glyphicon-menu-<?php if($_a[1]==0){ ?>down<?php }else{ ?>up<?php } ?>"></span><?php } ?></a></th>
			<th><a href="?dir=<?php echo $_DiskENV['dir']; ?>&sort=1<?php if($_a[0]==1)echo ($_a[1]+1)%2; else echo 0; ?>">大小<?php if($_a[0]==1){ ?><span class="glyphicon glyphicon-menu-<?php if($_a[1]==0){ ?>down<?php }else{ ?>up<?php } ?>"></span><?php } ?></th>
			<th><a href="?dir=<?php echo $_DiskENV['dir']; ?>&sort=2<?php if($_a[0]==2)echo ($_a[1]+1)%2; else echo 0; ?>">上傳時間<?php if($_a[0]==2){ ?><span class="glyphicon glyphicon-menu-<?php if($_a[1]==0){ ?>down<?php }else{ ?>up<?php } ?>"></span><?php } ?></a></th>
		</tr>
	</thead>
	<tbody>
	<?php
	do{
		switch(substr($_file['row']['type'],0,strpos($_file['row']['type'],'/'))){
			case 'image':
				$_icon='picture';
				break;
			case 'audio':
				$_icon='music';
				break;
			case 'video':
				$_icon='film';
				break;
			default:
				$_icon='file';
				break;
		}
	?>
	<tr data-id="<?php echo $_file['row']['id']; ?>" data-share="<?php echo $_file['row']['share_id']; ?>">
		<td width="20">
			<span class="glyphicon glyphicon-<?php echo $_icon; ?>"></span>
		</td>
		<td>
			<a href="readfile.php?id=<?php echo $_file['row']['share_id']; ?>"><?php echo $_file['row']['name']; ?></a>
			<?php if($_file['row']['share']==1){ ?>
			<span class="glyphicon glyphicon-globe"></span>
			<?php } ?>
		</td>
		<td><?php echo sd_size($_file['row']['size'],0); ?></td>
		<td><?php echo date('Y-m-d H:i',strtotime($_file['row']['mktime'])); ?></td>
		<td class="hidden-sm hidden-md hidden-lg"><span class="menu btn btn-info btn-xs">管理<span></td>
	</tr>
	<?php }while ($_file['row'] = $_file['query']->fetch_assoc()); ?>
	</tbody>
</table>
<?php
$_all_file=sd_get_result("SELECT COUNT(*) FROM `file` WHERE `dir`='%d' AND `owner`='%d'",array($_DiskENV['dir'],$_SESSION['Disk_Id']));
echo sd_page_pagination('',@$_GET['page'],implode('',$_all_file['row']),$limit_row,'&dir='.$_DiskENV['dir'].'&sort='.$_GET['sort']);
?>
<?php }else{ ?>
<div class="alert alert-danger">沒有檔案</div>
<?php } ?>