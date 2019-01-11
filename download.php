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
require_once('include/view.php');

if(!isset($_GET['id'])&&$_GET['id']==''){
	exit;
}

$_file=sd_get_result("SELECT * FROM `file` WHERE `share_id`='%s'",array($_GET['id']));

if($_file['num_rows']<1){
	header('Content-type: text/html; charset=utf-8');
	echo '此檔案不存在';
	exit;
}
if($_file['row']['share']!=1){
	header('Content-type: text/html; charset=utf-8');
	echo '此檔案不公開，僅限上傳者可下載';
	exit;
}

$_SESSION['Disk_Guest']=time();

$view = new View('include/theme/default.html','include/nav.php','',$disk['site_name'],$_file['row']['name'].' 下載');
?>
<div class="jumbotron">
	<h2><?php echo $_file['row']['name']; ?></h2>
	<p>
		<ul>
			<li>檔案大小：<?php echo sd_size($_file['row']['size']); ?></li>
			<li>上傳時間：<?php echo date('Y-m-d H:i',strtotime($_file['row']['mktime'])); ?>
		</ul>
	</p>
	<p><a href="readfile.php?id=<?php echo $_file['row']['share_id'];?>" class="btn btn-primary btn-lg">下載</a></p>
</div>
<?php $view->render(); ?>