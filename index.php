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

if(isset($_GET['logout'])) {
	sd_loginout();
	header("Location: login.php?out");
	exit;
}

if(!isset($_SESSION['Disk_Username'])){
	header("Location: login.php");
	exit;
}

$_DiskENV['dir']='0';
$_dir['row']['name']='主目錄';
if(isset($_GET['dir'])&&$_GET['dir']>0){
	$_dir=sd_get_result("SELECT * FROM `dir` WHERE `id`='%d' AND `owner` = '%d'",array($_GET['dir'],$_SESSION['Disk_Id']));
	if($_dir['num_rows']>0){
		$_DiskENV['dir']=$_dir['row']['id'];
	}else{
		exit;
	}
}

$_member=sd_get_result("SELECT * FROM `member` WHERE `id`='%d'",array($_SESSION['Disk_Id']));

$view = new View('include/theme/default.html','include/nav.php','',$disk['site_name'],'檔案');
$view->addScript('include/js/fileupload/jquery.ui.widget.js');
$view->addScript('include/js/fileupload/jquery.iframe-transport.js');
$view->addScript('include/js/fileupload/jquery.fileupload.js');
$view->addScript('include/js/filemanager.js');
?>
<script>
var auth='<?php echo $_SESSION['Disk_Auth']; ?>';
var banext=['<?php echo implode("','",$disk['upload']['banext']); ?>'];
var max_file_size=<?php echo $disk['upload']['max_size']; ?>*1000;
var dir=<?php echo $_DiskENV['dir']; ?>;
var page=<?php if(isset($_GET['page'])){echo abs($_GET['page']);}else{echo '0';} ?>;
var sort='<?php if(isset($_GET['sort'])){echo substr(intval($_GET['sort']),0,2);}else{echo '00';} ?>';
</script>
<div class="row">
	<div id="file_list" class="col-md-9">
		<div class="alert alert-info">載入中...請稍後...</div>
	</div>
	<div class="col-md-3">
		<div class="btn-group" style="width:100%;">
			<div class="btn btn-danger btn-block dropdown-toggle" data-toggle="dropdown">新增</div>
			<ul class="dropdown-menu">
				<li><a href="#uploadbox" data-toggle="modal" data-target="#uploadbox"><span class="glyphicon glyphicon-open-file">&nbsp;檔案上傳</a></li>
				<li role="separator" class="divider"></li>
				<li><a href="#mkdir" data-toggle="modal" data-target="#mkdir"><span class="glyphicon glyphicon-folder-open">&nbsp;新增資料夾</a></li>
			</ul>
		</div>
		<div class="panel panel-default">
			<div id="dir_list" class="panel-body"></div>
		</div>
		<div class="panel panel-default">
			<div id="space_info" class="panel-body text-center" style="font-size:88%;">
			使用了 <?php echo sd_size($_member['row']['file_space']); ?> 中的 <?php echo sd_size($_member['row']['used_space']); ?> (<?php echo round($_member['row']['used_space']/$_member['row']['file_space']*100,3); ?>%)
			<?php echo sd_space_progress($_member); ?>
			</div>
		</div>
	</div>
</div>
<div id="uploadbox" class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title">檔案上傳</h3>
			</div>
			<div class="modal-body">
				檔案大小限制：<?php echo sd_size($disk['upload']['max_size']*1000); ?>
				<div id="uploader">
					<div id="drop" class="well">
						<p>將檔案拖曳到這裡</p>
						<input id="fileupload" name="files" type="file" multiple>
					</div>
					<div id="uploadinfo">
						<div id="progress" class="progress" style="margin:0 auto;width: 90%;">
							<div class="progress-bar progress-bar-success" style="width: 0%;"></div>
						</div>
						<table class="table item">
							<tr>
								<th></th>
								<th>檔案</th>
								<th>進度</th>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a class="btn btn-default" data-dismiss="modal" aria-hidden="true"><span class="glyphicon glyphicon-remove"></span> 關閉</a>
			</div>
		</div>
	</div>
</div>
<div id="mkdir" class="modal fade">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title">新增資料夾</h3>
			</div>
			<div class="modal-body">
				<input class="form-control" name="name" type="text" required="required">
			</div>
			<div class="modal-footer">
				<span class="btn btn-success"><span class="glyphicon glyphicon-ok"></span> 建立</span>
				<a class="btn btn-default" data-dismiss="modal" aria-hidden="true"><span class="glyphicon glyphicon-remove"></span> 關閉</a>
			</div>
		</div>
	</div>
</div>
<div id="dir_rename" class="modal fade">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title">重新命名</h3>
			</div>
			<div class="modal-body">
				<input class="form-control" name="name" type="text" required="required">
			</div>
			<div class="modal-footer">
				<span class="btn btn-success"><span class="glyphicon glyphicon-ok"></span> 改名</span>
				<a class="btn btn-default" data-dismiss="modal" aria-hidden="true"><span class="glyphicon glyphicon-remove"></span> 關閉</a>
			</div>
		</div>
	</div>
</div>
<div id="rename" class="modal fade">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title">重新命名</h3>
			</div>
			<div class="modal-body">
				<div class="input-group">
					<input class="form-control" name="name" type="text" required="required">
					<span class="input-group-addon"></span>
				</div>
			</div>
			<div class="modal-footer">
				<span class="btn btn-success"><span class="glyphicon glyphicon-ok"></span> 改名</span>
				<a class="btn btn-default" data-dismiss="modal" aria-hidden="true"><span class="glyphicon glyphicon-remove"></span> 關閉</a>
			</div>
		</div>
	</div>
</div>
<div id="move" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title">移動</h3>
			</div>
			<div class="modal-body">
			</div>
			<div class="modal-footer">
				<span class="btn btn-success"><span class="glyphicon glyphicon-ok"></span> 確定</span>
				<a class="btn btn-default" data-dismiss="modal" aria-hidden="true"><span class="glyphicon glyphicon-remove"></span> 取消</a>
			</div>
		</div>
	</div>
</div>
<?php $view->render(); ?>