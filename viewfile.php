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
		if($_file['row']['share']==0){
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
	
	$_offset=-1;
	if(isset($_SERVER['HTTP_RANGE'])){
		$_range=explode('-',$_SERVER['HTTP_RANGE']);
		$_offset=$_range[0];
        if(!$_range[1]) {
            $_range[1] = $_file['row']['size'] - 1;
        }
		header('HTTP/1.1 206 Partial Content');
		header('Accept-Ranges: bytes');
		header('Content-Length: '.$_range[1]-$_range[0]);
		header("Content-Range: {$_range[0]}-{$_range[1]}/{$_file['row']['size']}");
	}else{
		header('Content-Length: '.$_file['row']['size']);
	}
	
	if(in_array(substr($_file['row']['type'],0,strpos($_file['row']['type'],'/')),array('audio','image','video'))){
		header('Content-type: ' . $_file['row']['type']);
	}else{
		header('Content-type: application/octet-stream');
	}
	
	echo sd_file_decode(sd_decode(base64_decode($_file['row']['private_key']),$disk['key']),'file/'.$_file['row']['server_name'].'.sdfile',$_file['row']['size'],$_offset);
	
	//readfile($file_path);
}else{
	echo '此檔案不存在';
}