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
 if(isset($_SESSION['Disk_Username'])){ ?>
	<li class="dropdown">
	<a href="#" data-target="#" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span>&nbsp;<?php echo $_SESSION['Disk_Username']; ?> ▼</a>
	<ul class="dropdown-menu">
		<li><a href="account.php">我的帳號</a></li>		
		<?php if($_SESSION['Disk_UserGroup']==9){?>
		<li><a href="admin/index.php">系統管理</a></li>
		<?php } ?>
		<li><a href="index.php?logout">登出</a></li>
	</ul>
</li>
<?php }else{ ?>
	<li><a href="index.php">登入</a></li>
	<li><a href="register.php">註冊</a></li>
<?php } ?>