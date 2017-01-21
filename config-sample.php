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

require_once('include/function.php');
if(!session_id()) {
	session_start();
}

if(!isset($_COOKIE['login']) && isset($_SESSION['Disk_Username'])){
	sd_loginout();//直接登出
	if(file_exists('index.php')){
		header('Location: index.php');
	}
	exit;
}

date_default_timezone_set("Asia/Taipei");//時區設定
$disk['site_name']='%s';//網站名稱
$disk['register']='%d';//是否開啟註冊 1為開啟  0為關閉
$disk['upload']['banext'] = array("%s");//禁止的副檔名
$disk['upload']['max_size'] = '%s';//單檔上傳大小限制 單位 KB
$disk['default']['file_space']='%s';//預設檔案儲存空間  單位 KB