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

function sd_ver(){
	return '1.2';
}

function sd_keygen($_value=''){
	return str_replace('=','',str_shuffle(base64_encode(mt_rand(0,time()).time()).hash('sha256',mt_rand().sha1($_value).uniqid())));
}
function sd_login($_username,$_password){
	global $SQL;
	global $disk;
	if (isset($_username)&&isset($_password)) {
		$login = $SQL->query("SELECT `id`,`username`, `password`, `file_space`, `level` FROM `member` WHERE (`username` = '%s' OR `email` = '%s') AND `password` = '%s'",array(
			$_username,
			$_username,
			sd_password($_password,$_username)
		));
		
		
		//[相容] 7.3 版以前密碼----開始
		if ($login->num_rows <1) {
			$login = $SQL->query("SELECT `id`,`username`, `password`, `file_space`, `level` FROM `member` WHERE (`username` = '%s' OR `email` = '%s') AND `password` = '%s'",array(
			$_username,
			$_username,
			md5(sha1($_password))
			));
			if($login->num_rows > 0){
				$SQL->query("UPDATE `member` SET `password` = '%s' WHERE `username` = '%s'",array(sd_password($_password,$_username),$_username));
			}
		}//[相容] 7.3 版以前密碼----結束
		
		
		if ($login->num_rows > 0) {
			$info = $login->fetch_assoc();
			
			$SQL->query("UPDATE `member` SET `last_login` = now() WHERE `username` = '%s'",array($info['username']));
			
			if($info['file_space']==0){
				$SQL->query("UPDATE `member` SET `file_space` = '%s' WHERE `username` = '%s'",array(abs($disk['default']['file_space']),$info['username']));
			}
			
			$_SESSION['Disk_Username'] = strtolower($_username);
			$_SESSION['Disk_Id'] = $info['id'];
			$_SESSION['Disk_UserGroup'] = $info['level'];
			$_SESSION['Disk_Auth'] = substr(sd_keygen(),0,5);
			setcookie("login", time(), time()+10800);
			return 1;
		}
		else {
			return -1;
		}
	}
}
function sd_loginout(){
	$_SESSION['Disk_Username'] = NULL;
	$_SESSION['Disk_Id'] = NULL;
	$_SESSION['Disk_UserGroup'] = NULL;
	$_SESSION['Disk_Auth'] = NULL;
	unset($_SESSION['Disk_Username']);
	unset($_SESSION['Disk_Id']);
	unset($_SESSION['Disk_UserGroup']);
	unset($_SESSION['Disk_Auth']);
	setcookie("login", "", time()-10800);
	return 1;
}

function sd_register($_username,$_password,$_email,$_file_space,$_level=1){
	global $SQL;
	global $disk;
	if($disk['register'] == 1){
		if(isset($_username) && (trim(sd_namefilter($_username)) != '') && isset($_password) && (trim($_password) != '')&& filter_var($_email, FILTER_VALIDATE_EMAIL)){
			
			$_username=sd_namefilter($_username);
			
			$auth_name = $SQL->query("SELECT `username` FROM `member` WHERE `username` = '%s' OR `email` = '%s'", array($_username,$_email));
			if($auth_name->num_rows > 0){
				return -1;
				exit;
			}
			
			$add_user = $SQL->query("INSERT INTO `member` (`username`, `password`, `email`, `used_space`,`file_space`,`level` , `joined` ,`last_login`) VALUES ('%s', '%s', '%s','0','%s', '%d', now(), now())",array(
				sd_namefilter($_username),
				sd_password($_password,$_username),
				$_email,
				$_file_space,
				$_level
			));
			
			return 1;
		}else{
			return -2;
		}
	}else{
		return -3;
	}
}

function sd_get_result($_query,$_value=array()){
	global $SQL;
	$_result['query'] = $SQL->query($_query,$_value);
	$_result['row'] = $_result['query']->fetch_assoc();
	$_result['num_rows'] = $_result['query']->num_rows;
	if($_result['num_rows']>0){
		return $_result;
	}else{
		return -1;
	}
}

function sd_member_level_array(){
	return array(1=>'一般會員',9=>'管理員');
}

function sd_member_level($_level){
	$_level_array=sd_member_level_array();
	return $_level_array[$_level];
}

function sd_namefilter($_value){
	$_array=array('/' => '' , '\\' => '' , '*' => '' ,':' => '' , '?' => '' , '<'  => '' , '>' => '','│' => '');
	return strtr($_value,$_array);
}

function sd_password($_value,$_salt){
	$salt=substr(sha1(strrev($_value).$_salt),0,24);
	return hash('sha512',$salt.$_value);
}

function sd_get_headurl(){
	$_prefix='http';
	if(isset($_SERVER['HTTPS'])){
		if($_SERVER['HTTPS'] == 'on'){
			$_prefix='https';
		}
	}
	$url="$_prefix://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
	$po= strripos($url,'/');
	return substr($url,0,$po).'/';
}
function sd_size($_size,$_precision=2){
	if($_size<0){
		$_size=abs($_size);
		$_sign='-';
	}else{
		$_sign='';
	}
	if($_size<1100){
		return $_sign.round($_size,$_precision).' Byte';
	}elseif($_size/1000<1100){
		return $_sign.round($_size/1000,$_precision) .' KB';
	}elseif($_size/1000/1000<1100){
		return $_sign.round($_size/1000/1000,$_precision) .' MB';
	}elseif($_size/1000/1000/1000<1100){
		return $_sign.round($_size/1000/1000/1000,$_precision) .' GB';
	}elseif($_size/1000/1000/1000/1000<1100){
		return $_sign.round($_size/1000/1000/1000/1000,$_precision) .' TB';
	}else{
		return $_sign.round($_size/1000/1000/1000/1000/1000,$_precision) .' PB';
	}
}
function sd_file_encode($_key,$origin_path,$new_path){
	$type=MCRYPT_RIJNDAEL_256;
	$mode=MCRYPT_MODE_CBC;
	$iv_size=mcrypt_get_iv_size($type,$mode);
	$key_size=mcrypt_get_key_size($type,$mode);
	$key=substr(hash('sha256',base64_encode(hash('sha512',$_key).$_key)),0,$key_size);
	$iv=substr(hash('sha512',str_rot13($_key),true),0,$iv_size);
	$opts = array('iv'=>$iv, 'key'=>$key);
	
	$fp = fopen($new_path, 'wb');// 開啟檔案	
	// 掛上MCrypt資料流過濾器
	// AES - 256 加密
	stream_filter_append($fp, 'mcrypt.rijndael-256', STREAM_FILTER_WRITE, $opts);
	stream_copy_to_stream(fopen($origin_path, 'rb'), $fp);// 把資料寫入檔案
	fclose($fp);// 關閉檔案
}

function sd_file_decode($_key,$file_path,$_size,$_offset=-1){
	$type=MCRYPT_RIJNDAEL_256;
	$mode=MCRYPT_MODE_CBC;
	$iv_size=mcrypt_get_iv_size($type,$mode);
	$key_size=mcrypt_get_key_size($type,$mode);
	//$block_size=mcrypt_get_block_size($type,$mode);
	$key=substr(hash('sha256',base64_encode(hash('sha512',$_key).$_key)),0,$key_size);
	$iv=substr(hash('sha512',str_rot13($_key),true),0,$iv_size);
	$opts = array('iv'=>$iv, 'key'=>$key);

	$fp = fopen($file_path, 'rb');// 開啟檔案
	// 掛上MCrypt資料流過濾器
	// AES - 256 加密
	stream_filter_append($fp, 'mdecrypt.rijndael-256', STREAM_FILTER_READ, $opts);
	return stream_get_contents($fp,$_size,$_offset);// 讀取資料
}

function sd_space_progress($_member,$_barwidth=100,$_content=''){
	$_width=round($_member['row']['used_space']/$_member['row']['file_space']*100,3);
	$_class='progress-bar-success';
	if($_width>80){
		$_class='progress-bar-danger';
	}elseif($_width>50){
		$_class='progress-bar-warning';
	}

	return '<div class="progress" style="width:'.$_barwidth.'%;"><div class="progress-bar '.$_class .'" style="width: '.$_width.'%;">'.$_content.'</div></div>';
}

function sd_page_pagination($_href,$_now_page,$_data_num,$_page_limit,$_href_parameters=''){
	$_return='';
	$_now_page=abs($_now_page);
	$page_num= ceil($_data_num / $_page_limit);
	if($page_num>=7){
		$_return.='<ul class="pagination">';
		$_array=array(1,2,3,$_now_page-2,$_now_page-1,$_now_page,$_now_page+1,$_now_page+2,$page_num-2,$page_num-1,$page_num);
		$_array=array_unique($_array);
		$_last_value=0;
		foreach ($_array as $value){
			if($value>0&&$value<=$page_num){
				if($_last_value+1!=$value){
					$_return.='<li><span>...</span></li>';
				}
				if($_now_page==$value){
					$_return.='<li class="active"><span>'.$value.'</span></li>';
				}else{
					$_return.='<li><a href="'.$_href.'?page='.$value.$_href_parameters.'">'.$value.'</a></li>';
				}
				$_last_value=$value;
			}
		}
		$_return.='</ul>';
	}elseif($page_num>1){
		$_return.='<ul class="pagination">';
		for($i=1;$i<=$page_num;$i++){
			if($_now_page!=$i){
				$_return.='<li><a href="'.$_href.'?page='.$i.$_href_parameters.'">'.$i.'</a></li>';
			}else{
				$_return.='<li class="active"><span>'.$i.'</span></li>';
			}
		}
		$_return.='</ul>';
	}
	return $_return;
}

function sd_dir_list($_dir_list,$_now,$_id){
	global $SQL;
	$_return='';
	$_class='';
	$_icon='<span class="glyphicon glyphicon-folder-close"></span>&nbsp;';
	$_dir=sd_get_result("SELECT * FROM `dir` WHERE `parent`='%d' AND `owner`='%d' ORDER BY `name` ASC",array($_dir_list['row']['id'],$_id));
	if($_now==$_dir_list['row']['id']){ 
		$_class=' class="text-danger"';
		$_icon='<span class="glyphicon glyphicon-folder-open"></span>&nbsp;';
	}
	$_return.='<li><a href="?dir='.$_dir_list['row']['id'].'"'.$_class.'>'.$_icon.$_dir_list['row']['name'].'</a>';
	
	if($_dir_list['row']['share']==1){
		$_return.=' <span class="glyphicon glyphicon-globe" style="font-size:10px;"></span>';
	}
	
	if($_dir['num_rows']>0){
		$_return.='<ul class="list-unstyled">';
		do{
			$_return.=sd_dir_list($_dir,$_now,$_id);
		}while ($_dir['row'] = $_dir['query']->fetch_assoc());
		$_return.='</ul>';
	}
	$_return.='</li>';
	return $_return;
}
function sd_dir_list_form($_dir_list,$_not,$_id){
	global $SQL;
	$_return='';
	$_class='';
	$_disabled='';
	$_dir=sd_get_result("SELECT * FROM `dir` WHERE `parent`='%d' AND `owner`='%d' ORDER BY `name` ASC",array($_dir_list['row']['id'],$_id));
	if(is_array($_not)){
		if(in_array($_dir_list['row']['id'],$_not)){ 
			$_class=' class="text-muted"';
			$_disabled=' disabled';
		}
	}else{
		if($_not==$_dir_list['row']['id']){ 
			$_class=' class="text-muted"';
			$_disabled=' disabled';
		}
	}
	$_return.='<li><label><input type="radio" name="dir" value="'.$_dir_list['row']['id'].'"'.$_disabled.'><span'.$_class.'> '.$_dir_list['row']['name'].'</span></label>';
	
	if($_dir_list['row']['share']==1){
		$_return.=' <span class="glyphicon glyphicon-globe" style="font-size:10px;"></span>';
	}
	
	if($_dir['num_rows']>0){
		$_return.='<ul class="list-unstyled">';
		do{
			$_return.=sd_dir_list_form($_dir,$_not,$_id);
		}while ($_dir['row'] = $_dir['query']->fetch_assoc());
		$_return.='</ul>';
	}
	$_return.='</li>';
	return $_return;
	
}
function sd_dir_path($_dir){
	global $SQL;
	
	$_return=array();
	$_return[]='<li class="active">'.$_dir['row']['name'].'</li>';
	
	$_dir_parent=sd_get_result("SELECT `id`,`name`,`parent` FROM `dir` WHERE `id`='%d'",array($_dir['row']['parent']));
	
	if($_dir_parent['num_rows']>0){
		$_return[]='<li><a href="?dir='.$_dir_parent['row']['id'].'">'.$_dir_parent['row']['name'].'</a></li>';
		while($_dir_parent['row']['parent']!=0){
			$_dir_parent=sd_get_result("SELECT `id`,`name`,`parent` FROM `dir` WHERE `id`='%d'",array($_dir_parent['row']['parent']));
			$_return[]='<li><a href="?dir='.$_dir_parent['row']['id'].'">'.$_dir_parent['row']['name'].'</a></li>';
		}
	}
	
	return implode('',array_reverse($_return));
}
function sd_dir_child($_dir){
	global $SQL;
	$_child[]=$_dir;
	$_next_dir=sd_get_result("SELECT `id` FROM `dir` WHERE `parent`='%d'",array($_dir));
	if($_next_dir['num_rows']>0){
		do{
			$_child[]=sd_dir_child($_next_dir['row']['id']);
		}while($_next_dir['row']=$_next_dir['query']->fetch_assoc());
	}
	return implode(',',$_child);
	
}
function sd_dir_delete($_dir,$_id,$_path='./'){
	global $SQL;
	$_dir_id=sd_dir_child($_dir);
	$_space=0;
	$_file=sd_get_result("SELECT `server_name`,`size` FROM `file` WHERE `dir` IN (%s) AND `owner`='%d'",array($_dir_id,$_id));
	if($_file['num_rows']>0){
		do{
			@unlink($_path.'file/'.$_file['row']['server_name'].'.sdfile');
			$_space+=$_file['row']['size'];
		}while($_file['row']=$_file['query']->fetch_assoc());
	}
	$SQL->query("UPDATE `member` SET `used_space` = `used_space`-'%d' WHERE `id` = '%d'",array($_space,$_id));
	$SQL->query("DELETE FROM `file` WHERE `dir` IN(%s)",array($_dir_id));
	$SQL->query("DELETE FROM `dir` WHERE `id` IN(%s)",array($_dir_id));
}


function sd_encode($_value,$_key){
	$type=MCRYPT_RIJNDAEL_256;
	$mode=MCRYPT_MODE_CBC;
	$iv_size=mcrypt_get_iv_size($type,$mode);
	$key_size=mcrypt_get_key_size($type,$mode);
	$key=substr(hash('sha1',$_key),0,$key_size);
	$iv=substr(hash('md5',$_key),0,$iv_size);
	
	return mcrypt_encrypt($type,$key,$_value,$mode,$iv);

}

function sd_decode($_value,$_key){
	$type=MCRYPT_RIJNDAEL_256;
	$mode=MCRYPT_MODE_CBC;
	$iv_size=mcrypt_get_iv_size($type,$mode);
	$key_size=mcrypt_get_key_size($type,$mode);
	$key=substr(hash('sha1',$_key),0,$key_size);
	$iv=substr(hash('md5',$_key),0,$iv_size);
	
	return rtrim(mcrypt_decrypt($type,$key,$_value,$mode,$iv));
}