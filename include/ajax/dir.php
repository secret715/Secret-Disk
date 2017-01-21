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

set_include_path('../../include/');
$includepath = true;
require_once('../../config.php');
require_once('../../Connections/SQL.php');
if(!isset($_SESSION['Disk_Username'])){
	exit;
}

$_DiskENV['dir']='0';
if(isset($_GET['dir'])&&$_GET['dir']>0){
	$_dir=sd_get_result("SELECT `id` FROM `dir` WHERE `id`='%d' AND `owner` = '%d'",array($_GET['dir'],$_SESSION['Disk_Id']));
	if($_dir['num_rows']>0){
		$_DiskENV['dir']=$_dir['row']['id'];
	}
}

$_data=array();
if(isset($_GET['mkdir'])&&isset($_POST['name'])&&trim($_POST['name'])!=''){
	$SQL->query("INSERT INTO `dir` (`name`, `parent`, `share`, `share_id`, `mktime`, `owner`) VALUES ('%s', '%d', 0, '%s', now(), '%d')",array(sd_namefilter($_POST['name']),$_DiskENV['dir'],substr(sd_keygen(mt_rand()),0,10),$_SESSION['Disk_Id']));
	$_data['status']='success';
}elseif(isset($_GET['del'])){
	$_data['status']='success';
	sd_dir_delete($_DiskENV['dir'],$_SESSION['Disk_Id'],'../../');
	
	$_member=sd_get_result("SELECT `used_space`,`file_space` FROM `member` WHERE `id`='%d'",array($_SESSION['Disk_Id']));
	$_data['info']='使用了 '.sd_size($_member['row']['file_space']).' 中的 '.sd_size($_member['row']['used_space']).' ('.round($_member['row']['used_space']/$_member['row']['file_space']*100,3).'%)'.sd_space_progress($_member);
	
}elseif(isset($_GET['rename'])&&isset($_POST['name'])&&trim($_POST['name'])!=''){
	$_data['status']='success';
	$SQL->query("UPDATE `dir` SET `name` = '%s' WHERE `id` = '%d' AND `owner` = '%d'",array(sd_namefilter($_POST['name']),$_DiskENV['dir'],$_SESSION['Disk_Id']));
}elseif(isset($_GET['move'])&&isset($_GET['dir'])){
	if(!in_array($_GET['move'],explode(',',sd_dir_child($_DiskENV['dir'])))){
		$_data['status']='success';
		$SQL->query("UPDATE `dir` SET `parent` = '%d' WHERE `id`='%d' AND `owner` = '%d'",array($_GET['move'],$_DiskENV['dir'],$_SESSION['Disk_Id']));
	}else{
		$_data['status']='error';
	}
}elseif(isset($_GET['share'])&&isset($_GET['dir'])){
	$_data['status']='success';
	$SQL->query("UPDATE `dir` SET `share` = '1' WHERE `id` IN(%s) AND `owner` = '%d'",array(sd_dir_child($_DiskENV['dir']),$_SESSION['Disk_Id']));
	$SQL->query("UPDATE `file` SET `share` = '1' WHERE `dir` IN(%s) AND `owner` = '%d'",array(sd_dir_child($_DiskENV['dir']),$_SESSION['Disk_Id']));
}elseif(isset($_GET['unshare'])&&isset($_GET['dir'])){
	$_data['status']='success';
	$SQL->query("UPDATE `dir` SET `share` = '0' WHERE `id` IN(%s) AND `owner` = '%d'",array(sd_dir_child($_DiskENV['dir']),$_SESSION['Disk_Id']));
	$SQL->query("UPDATE `file` SET `share` = '0' WHERE `dir` IN(%s) AND `owner` = '%d'",array(sd_dir_child($_DiskENV['dir']),$_SESSION['Disk_Id']));
}
echo json_encode($_data);
exit;