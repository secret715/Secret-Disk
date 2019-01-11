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

require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/key.php');

if(isset($_GET['id'])&&$_GET['id']!=''){
	$_file=sd_get_result("SELECT * FROM `file` WHERE `share_id`='%s'",array($_GET['id']));
	
	if($_file['num_rows']<1){
		exit;
	}
	if(!isset($_SESSION['Disk_Id'])){
		if($_file['row']['share']==1){		
			if(!isset($_SESSION['Disk_Guest'])or$_SESSION['Disk_Guest']<time()-60*5){
				header("Location: download.php?id=".$_file['row']['share_id']);
				exit;
			}
			unset($_SESSION['Disk_Guest']);
		}else{
			header('Content-Type:text/html; charset=utf-8');
			echo '此檔案不公開';
			exit;
		}
	}else{
		if($_file['row']['share']==0){
			if($_file['row']['owner']!=$_SESSION['Disk_Id']){
				header('Content-Type:text/html; charset=utf-8');
				echo '此檔案不公開';
				exit;
			}
		}
	}
	
	if(isset($_SERVER['HTTP_USER_AGENT']) && (substr_count(strtolower($_SERVER['HTTP_USER_AGENT']),'edge')+substr_count(strtolower($_SERVER['HTTP_USER_AGENT']),'msie')>0)){
		$_file_name=mb_convert_encoding(strtr($_file['row']['name'],' ','_'),'big5','utf-8');
	}else{
		$_file_name=strtr($_file['row']['name'],' ','_');
	}
	
	header('Content-type: application/octet-stream');
	header('Content-Transfer-Encoding: Binary');
	header("Content-Disposition: attachment; filename= ".$_file_name);
	
	echo sd_file_decode(sd_decode(base64_decode($_file['row']['private_key']),$disk['key']),'file/'.$_file['row']['server_name'].'.sdfile',$_file['row']['size']);
	
	//readfile($file_path);
}else{
	echo '此檔案不存在';
}