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

if(isset($_POST['username'])){
	if(sd_login($_POST['username'],$_POST['password'])==1){
		header("Location: index.php");
	}else{
		header("Location: login.php?login");
	}
	die();
}
else if(isset($_GET['logout'])) {
	sd_loginout();
}

if(isset($_SESSION['Disk_Username'],$_SESSION['Disk_UserGroup'])){
	header('Location: index.php');
}

$view = new View('include/theme/login.html',NULL,NULL,$disk['site_name'],'登入');

switch(true){
	case isset($_GET['reg']):
?>
	<div class="alert alert-success">註冊成功！</div>
<?php
	break;
	case isset($_GET['out']):
?>
	<div class="alert alert-success">您已經登出！</div>
<?php
	break;
	case isset($_GET['login']):
?>
	<div class="alert alert-danger">登入失敗</div>
<?php
	break;
}
?>
<form id="loginbox" action="login.php" method="post">
	<h2 class="text-center"><?php echo $disk['site_name']; ?></h2>
	<div class="form-group">
		<label class="control-label" for="username">帳號：</label>
		<input class="form-control" name="username" type="text" required="required">
	</div>
	<div class="form-group">
		<label class="control-label" for="password">密碼：</label>
		<input class="form-control" name="password" type="password" required="required">
	</div>
	<div class="form-group">
		<div class="btn-group btn-group-justified">
			<div class="btn-group">
				<input class="btn btn-primary btn-lg" type="submit" value="登入">
			</div>
			<?php if($disk['register'] == 1){ ?>
			<div class="btn-group">
				<a href="register.php" class="btn btn-info btn-lg">註冊</a>
			</div>
			<?php } ?>
		</div>
	</div>
</form>
<?php $view->render(); ?>