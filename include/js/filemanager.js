$(function(){
	$('#uploadinfo').hide();
	$('#fileupload').fileupload({
		dropZone: $('#drop'),
		url: 'include/ajax/upload.php?dir='+dir,
		dataType: 'json',
		add: function (e, data) {
				$('#uploadinfo').show();
				var tpl = $('<tr class="warning"><td class="file-cancel"><span class="glyphicon glyphicon-remove"></span></td><td class="file-info"></td><td class="file-pro"></td></tr>');
				tpl.find('.file-info').text(data.files[0].name);
				data.context = tpl.appendTo($('.item'));
				
				$('#progress .bar').width(0).text('');
				
				var extend=data.files[0].name.split('.').pop();
				
				for(var i=0;i<banext.length;i++){
					if(banext[i]==extend){
						var in_array = true;
						break;
					}else{
						var in_array = false;
					}
				}
				
				var error=0;
				if(in_array==true){
					alert(data.files[0].name+' 不允許此格式');
					error=1;
				}
				if(data.files[0].size>max_file_size){
					alert(data.files[0].name+' 的大小過大');
					error=1;
				}
				if(error==0){
					var jqXHR = data.submit();
				}else{
					tpl.remove();
				}
				
				tpl.find('.file-cancel').click(function(){
					tpl.fadeOut(function(){
						if(tpl.hasClass('warning')){
							jqXHR.abort();  //終止上傳
						}
						tpl.remove();
					});
				});
			},

			//單一檔案進度
			progress: function(e, data){
				var progress = parseInt(data.loaded / data.total * 100, 10);
				data.context.find('.file-pro').text(progress+'%').change();
				if(progress == 100){
					data.context.addClass('success').removeClass('warning');
					data.context.find('.file-pro').text('完成');
				}
			},

			//總進度
			progressall: function (e, data) {
				var progress = parseInt(data.loaded / data.total * 100, 10);
				$('#progress .progress-bar').css('width', progress + '%');
				$('#progress .progress-bar').text(progress + '%');
			},

			//上傳失敗
			fail:function(e, data){
				data.context.addClass('danger');
				data.context.find('.file-pro').text('失敗');
			},

			//單一檔案上傳完成
			done: function (e, data) {
				if(data.result.status=='error'){
					data.context.addClass('danger');
					data.context.find('.file-pro').text(data.result.msg);
				}
			}
	});
	$("#drop").bind({
		dragenter: function() {
			$(this).addClass('active');
		},
		dragleave: function() {
			$(this).removeClass('active');
		}
	});
	
	$('#uploadbox').on('hide.bs.modal', function (e) {			
		sd_file_list(dir);
	});
	
	
	
	$('#mkdir span.btn.btn-success').click(function(){
		var self=$(this);
		if($('#mkdir input[name="name"]').val()!=''&&!self.hasClass('disabled')){
			self.addClass('disabled');
			$('#mkdir input[name="name"]').attr('disabled','disabled');
			$('#mkdir .modal-body').prepend('<div class="alert alert-info">新增中，請稍後...</div>');
			$.ajax({
				url:'include/ajax/dir.php?mkdir&dir='+dir,
				type: 'POST',
				data: {name:$('#mkdir input[name="name"]').val()},
				dataType: 'json',
				success: function(data){
					if(data.status=='success'){
						$('#mkdir .alert').removeClass('alert-info').addClass('alert-success').text('新增成功！');
						sd_dir_list();
						setTimeout(function(){
							$('#mkdir').modal('hide');
							$('#mkdir .alert').remove();
							self.removeClass('disabled');
							$('#mkdir input[name="name"]').attr('disabled',false).val('');
							},1500
						);
					}
				}
			});
		}
	});
	
	
	$(document.body).on('click','#file_list .breadcrumb .del',function(e){
		e.preventDefault();
		var self=$(this);
		if(window.confirm("確定刪除？")){
			$.ajax({
				url:'include/ajax/dir.php?del&dir='+dir,
				type: 'GET',
				dataType: 'json',
				success: function(data){
					if(data.status=='success'){
						sd_dir_list(dir);
						sd_file_list(dir);
						$('#space_info').html(data.info);
					}
				}
			});
		}
	});
	
	$(document.body).on('click','#file_list .breadcrumb .share',function(e){
		e.preventDefault();
		$.ajax({
			url:'include/ajax/dir.php?share&dir='+dir,
			type: 'GET',
			dataType: 'json',
			success: function(data){
				if(data.status=='success'){
					sd_dir_list(dir);
					sd_file_list(dir);
				}
			}
		});
	});
	$(document.body).on('click','#file_list .breadcrumb .unshare',function(e){
		e.preventDefault();
		$.ajax({
			url:'include/ajax/dir.php?unshare&dir='+dir,
			type: 'GET',
			dataType: 'json',
			success: function(data){
				if(data.status=='success'){
					sd_dir_list(dir);
					sd_file_list(dir);
				}
			}
		});
	});
	

	$(document.body).on('click','#file_list .breadcrumb .rename',function(e){
		e.preventDefault();
		var modal=$('#dir_rename');
		var $t=$(this);
		var id=dir;
		var name=$('.breadcrumb > li:last').text();
		modal.modal('show');
		modal.find('input[name="name"]').val(name);
		
		modal.find('span.btn.btn-success').one('click',function(){
			var self=$(this);
			if(modal.find('input[name="name"]').val()!=''&&!self.hasClass('disabled')){
				self.addClass('disabled');
				modal.find('input[name="name"]').attr('disabled','disabled');
				modal.find('.modal-body').prepend('<div class="alert alert-info">正在重新命名...</div>');
				$.ajax({
					url:'include/ajax/dir.php?rename&dir='+id,
					type: 'POST',
					data: {name:modal.find('input[name="name"]').val()},
					dataType: 'json',
					success: function(data){
						if(data.status=='success'){
							new_name=modal.find('input[name="name"]').val();
							modal.find('.alert').removeClass('alert-info').addClass('alert-success').text('修改成功！');
							$('.breadcrumb > li:last').text(new_name);
							sd_dir_list(dir);
							setTimeout(function(){
								modal.modal('hide').find('.alert').remove();
								self.removeClass('disabled');
								modal.find('input[name="name"]').attr('disabled',false).val('');
								},1500
							);
						}
					}
				});
			}
		});
	});
	
	
	$(document.body).on('click','#file_list .breadcrumb .move',function(e){
		e.preventDefault();
		var modal=$('#move');
		var id=dir;
		modal.attr('data-type','dir').modal('show').find('span.btn.btn-success').one('click',function(){
			var self=$(this);
			if(!self.hasClass('disabled')){
				self.addClass('disabled');
				modal.find('.modal-body').prepend('<div class="alert alert-info">正在移動...</div>');
				$.ajax({
					url:'include/ajax/dir.php?move='+modal.find('input[name="dir"]:checked').val()+'&dir='+dir,
					type: 'GET',
					dataType: 'json',
					success: function(data){
						if(data.status=='success'){
							sd_dir_list(dir);
							sd_file_list(dir);
							modal.find('.alert').removeClass('alert-info').addClass('alert-success').text('移動成功！');
						}else{
							modal.find('.alert').removeClass('alert-info').addClass('alert-danger').text('移動失敗');
						}
						setTimeout(function(){
							modal.modal('hide').find('.alert').remove();
							self.removeClass('disabled');
							},1500
						);
					}
				});
			}
		});
	});
	
	
	$(document.body).on('click','#file_list table .del',function(e){
		e.preventDefault();
		var self=$(this);
		if(window.confirm("確定刪除？")){
			$.ajax({
				url:'include/ajax/file.php?del&id='+$(this).parents('tr').attr('data-id'),
				type: 'GET',
				dataType: 'json',
				success: function(data){
					if(data.status=='success'){
						self.parents('tr').remove();
						$('#space_info').html(data.info);
					}
				}
			});
		}
	});
	
	$(document.body).on('click','#file_list table .share',function(e){
		e.preventDefault();
		$.ajax({
			url:'include/ajax/file.php?share&id='+$(this).parents('tr').attr('data-id'),
			type: 'GET',
			dataType: 'json',
			success: function(data){
				if(data.status=='success'){
					sd_file_list(dir);
				}
			}
		});
	});
	$(document.body).on('click','#file_list table .unshare',function(e){
		e.preventDefault();
		$.ajax({
			url:'include/ajax/file.php?unshare&id='+$(this).parents('tr').attr('data-id'),
			type: 'GET',
			dataType: 'json',
			success: function(data){
				if(data.status=='success'){
					sd_file_list(dir);
				}
			}
		});
	});
	$(document.body).on('click','#file_list table .rename',function(e){
		e.preventDefault();
		var modal=$('#rename');
		var $t=$(this);
		var id=$(this).parents('tr').attr('data-id');
		var name=$(this).attr('data-name').substring(0,$(this).attr('data-name').lastIndexOf('.'));
		var ext=$(this).attr('data-name').substring($(this).attr('data-name').lastIndexOf('.'));
		modal.modal('show');
		modal.find('input[name="name"]').val(name);
		modal.find('.input-group-addon').text(ext);
		
		modal.find('span.btn.btn-success').one('click',function(){
			var self=$(this);
			if(modal.find('input[name="name"]').val()!=''&&!self.hasClass('disabled')){
				self.addClass('disabled');
				modal.find('input[name="name"]').attr('disabled','disabled');
				modal.find('.modal-body').prepend('<div class="alert alert-info">正在重新命名...</div>');
				$.ajax({
					url:'include/ajax/file.php?rename&id='+id,
					type: 'POST',
					data: {name:modal.find('input[name="name"]').val()},
					dataType: 'json',
					success: function(data){
						if(data.status=='success'){
							new_name=modal.find('input[name="name"]').val()+ext;
							modal.find('.alert').removeClass('alert-info').addClass('alert-success').text('修改成功！');
							$('tr[data-id='+id+'] td:eq(1) a').text(new_name);
							$t.attr('data-name',new_name);
							
							setTimeout(function(){
								modal.modal('hide').find('.alert').remove();
								self.removeClass('disabled');
								modal.find('input[name="name"]').attr('disabled',false).val('');
								},1500
							);
						}
					}
				});
			}
		});
	});
	
	$(document.body).on('click','#file_list table .move',function(e){
		e.preventDefault();
		var modal=$('#move');
		var id=$(this).parents('tr').attr('data-id');
		modal.attr('data-type','file').modal('show').find('span.btn.btn-success').one('click',function(){
			var self=$(this);
			if(!self.hasClass('disabled')){
				self.addClass('disabled');
				modal.find('.modal-body').prepend('<div class="alert alert-info">正在移動...</div>');
				$.ajax({
					url:'include/ajax/file.php?move&id='+id+'&dir='+modal.find('input[name="dir"]:checked').val(),
					type: 'GET',
					dataType: 'json',
					success: function(data){
						if(data.status=='success'){
							sd_dir_list(dir);
							sd_file_list(dir);
							modal.find('.alert').removeClass('alert-info').addClass('alert-success').text('移動成功！');
							
							setTimeout(function(){
								modal.modal('hide').find('.alert').remove();
								self.removeClass('disabled');
								},1500
							);
						}
					}
				});
			}
		});
	});
	
	$('#move').on('show.bs.modal', function (e) {
		if($('#move').attr('data-type')=='dir'){
			var type='&child';
		}else{
			var type='';
		}
		$.ajax({
			url:'include/ajax/dir_list.php?form&dir='+dir+type,
			type: 'GET',
			dataType: 'html',
			success: function(data){
				if(data!=''){
					$('#move .modal-body').html(data);
				}
			}
		});
	});
	
	function sd_dir_list(dir){
		$.ajax({
			url:'include/ajax/dir_list.php?dir='+dir,
			type: 'GET',
			dataType: 'html',
			success: function(data){
				if(data!=''){
					$('#dir_list').html(data);
				}
			}
		});
	}
	function sd_file_list(dir){
		$.ajax({
			url:'include/ajax/file_list.php?dir='+dir+'&page='+page,
			type: 'GET',
			dataType: 'html',
			success: function(data){
				if(data!=''){
					$('#file_list').html(data);
				}
			}
		});
	}
	sd_dir_list(dir);
	sd_file_list(dir);
	
	
	$(document.body).on('click','#dir_list a,.breadcrumb > li a',function(e){
		e.preventDefault();
		dir=$(this).attr('href').substring($(this).attr('href').lastIndexOf('?dir=')+5);
		sd_dir_list(dir);
		sd_file_list(dir);
		$('#fileupload').fileupload({url: 'include/ajax/upload.php?dir='+dir});
	});
	
});