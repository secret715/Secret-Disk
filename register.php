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

if($disk['register'] == 1){
	if(isset($_POST['username']) && trim($_POST['username']) != ''){
		$reg=sd_register($_POST['username'],$_POST['password'],$_POST['email'],$disk['default']['file_space']*1000);
		if($reg>0){
			header("Location: login.php?reg");
		}elseif($reg==-1){
			$_GET['requsername']=true;
		}
	}
}else{
	header("Location: login.php");
}
$view = new View('include/theme/login.html',NULL,NULL,$disk['site_name'],'註冊');
?>
<?php if(isset($_GET['requsername'])){ ?>
	<div class="alert alert-danger">此帳號或電子信箱已被使用！</div>
<?php } ?>
<form id="loginbox" action="register.php" method="post">
	<h2 class="text-center"><?php echo $disk['site_name']; ?> - 註冊</h2>
	<div class="form-group">
		<label class="control-label" for="username">帳號：</label>
		<input class="form-control" name="username" type="text" required="required">
	</div>
	<div class="form-group">
		<label class="control-label" for="password">密碼：</label>
		<input class="form-control" name="password" type="password" required="required">
	</div>
	<div class="form-group">
		<label class="control-label" for="email">電子信箱：</label>
		<input class="form-control" name="email" type="email" required="required">
	</div>
	<div class="form-group">
		<div class="btn-group btn-group-justified">
			<div class="btn-group">
				<input class="btn btn-primary btn-lg" type="submit" value="註冊">
			</div>
			<div class="btn-group">
				<a href="login.php" class="btn btn-info btn-lg">登入</a>
			</div>
		</div>
	</div>
</form>
<?php $view->render(); ?>