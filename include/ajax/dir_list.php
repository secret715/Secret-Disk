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
$_dir_list = sd_get_result("SELECT * FROM `dir` WHERE `parent`='%d' AND `owner`='%d' ORDER BY `name` ASC",array(0,$_SESSION['Disk_Id']));

if(isset($_GET['form'])){
	if(isset($_GET['child'])){
		$_DiskENV['dir']=explode(',',sd_dir_child($_DiskENV['dir']));
	}
?>
<ul class="list-unstyled">
	<li><label><input type="radio" name="dir" value="0"><span class="text-success"> 主目錄</span></label></li>
	<?php
	if($_dir_list['num_rows']>0){
		do{
			echo sd_dir_list_form($_dir_list,$_DiskENV['dir'],$_SESSION['Disk_Id']);
		}while ($_dir_list['row'] = $_dir_list['query']->fetch_assoc());
	}
	?>
</ul>
<?php }else{ ?>
<ul class="list-unstyled">
	<li><span class="glyphicon glyphicon-home"></span>  <a href="?dir=0" class="text-success">主目錄</a></li>
	<?php
	if($_dir_list['num_rows']>0){
		do{
			echo sd_dir_list($_dir_list,$_DiskENV['dir'],$_SESSION['Disk_Id']);
		}while ($_dir_list['row'] = $_dir_list['query']->fetch_assoc());
	}
	?>
</ul>
<?php } ?>