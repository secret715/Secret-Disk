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

set_include_path('../../include/');
$includepath = true;
require_once('../../Connections/SQL.php');
require_once('../../config.php');

if(!isset($_SESSION['Disk_Username'])){
	exit;
}

$_DiskENV['dir']='0';
if(isset($_GET['dir']) && $_GET['dir']>0){
	$_dir=sd_get_result("SELECT `id` FROM `dir` WHERE `id`='%d' AND `owner` = '%d'",array($_GET['dir'],$_SESSION['Disk_Id']));
	if($_dir['num_rows']>0){
		$_DiskENV['dir']=$_dir['row']['id'];
	}
}
unset($_dir);

$_member=sd_get_result("SELECT * FROM `member` WHERE `id`='%d'",array($_SESSION['Disk_Id']));

$_Input=@$_FILES['files'];

if(isset($_Input) && $_Input['error'] == 0){
	$_dir='../../file';
	$_max_upload_size = $disk['upload']['max_size']*1000;//單位Byte
	$_extend = pathinfo($_Input['name'], PATHINFO_EXTENSION);//文件副檔名
	$_name=sd_namefilter(mb_substr(rtrim($_Input['name'],'.'.$_extend),0,250,'utf-8')).'.'.$_extend;//原檔案名稱
	$_server_name=substr(sd_keygen(),0,10).'.sdfile';//伺服器檔案名稱
	$_private_key=hash('sha512',sd_keygen(mt_rand().$_server_name));
	
	if(!is_dir("$_dir/")) {  //檢查資料夾是否存在
		if(!mkdir("$_dir/")){  //不存在的話就創建資料夾
			echo '{"status":"error","msg":"新增資料夾失敗"}';
			exit;
		}
	}
	if(file_exists($_dir.'/'.$_server_name)){
		for($i=0; $i<100; $i++){
			$_server_name=substr(sd_keygen(),0,10+round($i*0.1)).'.sdfile';
			if(file_exists($_dir.'/'.$_server_name)){
				continue;
			}else{
				break;
			}
		}
	}
	if(in_array($_extend,$disk['upload']['banext'])){
		echo '{"status":"error","msg":"不允許此格式"}';
		exit;
	}
	if($_max_upload_size <= $_Input['size']){
		echo '{"status":"error","msg":"超過檔案大小限制"}';
		exit;
	}
	if($_member['row']['used_space']+$_Input['size']>$_member['row']['file_space']){echo '{"status":"error","msg":"儲存空間不足"}';
		exit;
	}
	
	
	$SQL->query("INSERT INTO `file` (`name`, `server_name`, `size`, `type`, `dir`, `private_key`, `share`, `share_id`, `mktime`, `owner`) VALUES ('%s', '%s', '%d', '%s', '%d', '%s', 0, '%s', now(), '%d')",array(
	$_name,
	substr($_server_name,0,-7),
	$_Input['size'],
	$_Input['type'],
	$_DiskENV['dir'],
	$_private_key,
	substr(sd_keygen(mt_rand()),0,10),
	$_SESSION['Disk_Id']));
	
	$SQL->query("UPDATE `member` SET `used_space` = `used_space`+'%d' WHERE `id` = '%d'",array($_Input['size'],$_SESSION['Disk_Id']));
	
	sd_file_encode($_private_key,$_FILES['files']['tmp_name'],$_dir.'/'.$_server_name);
	echo '{"status":"success"}';
}else{
	echo '{"status":"error","msg":"上傳失敗，錯誤代碼：'. $_Input['error'].'"}';
}
exit;