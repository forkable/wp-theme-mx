define(function(require, exports, module){
'use strict';
exports.init = function(config){
	var defaults = {
		$file : false,
		url : '',
		paramname : 'file',
		maxsize : 1048*1024*2,
		maxfiles : 50,
		interval : 3000,
		onselect : function(e){},
		onstart : function(i,file,count){},
		onalways : function(data,i,file,count){},
		ondone : function(data,i,file,count){},
		onprogress : function(e){},
		onerror : function(data,i,file,count){}
	},
	config = jQuery.extend({},defaults,config);

	if(!config.$file[0]) return false;
	config.$file.on({
		change : select,
		drop : select
	});
	
	var files,
		file,
		file_count = 0,
		file_index = 0,
		start_time,
		is_uploading = false,
		all_complete = false;
	
	function select(e){
		e.stopPropagation();  
		e.preventDefault();  

		files = e.target.files.length ? e.target.files : e.originalEvent.dataTransfer.files;
		file_count = files.length;
		file = files[0];
		file_index = 0;

		upload(file);
	}
	function upload(file){
		start_time = new Date();
		var	reader = new FileReader();
		reader.onload = function (e) {
			config.onstart(file_index,file,file_count,e);
			submission(file);
		};
		reader.readAsDataURL(file);	
	}
	function submission(file){
		if(is_uploading) return;
		is_uploading = true;
		var fd = new FormData(),
		xhr = new XMLHttpRequest();
		fd.append(config.paramname,file);
		xhr.open('post',config.url);
		xhr.onload = complete;
		xhr.onreadystatechange = function(){
			if (xhr && xhr.readyState === 4) {
				status = xhr.status;
				if (status >= 200 && status < 300 || status === 304) {
				
				}else{
					error(status,file_index,file,file_count);
				}
			}
			is_uploading = false;
			xhr = null;
		}
		xhr.upload.onprogress = function(e){
			if (e.lengthComputable) {
				//var percent = e.loaded / e.total * 100;		
				config.onprogress(e)			
			}
		}
		xhr.send(fd);		
	}
	function complete(){
		var data = this.responseText;
		try{
			data = jQuery.parseJSON(this.responseText);
		}catch(error){
			data = false;
		}
		file_index++;
		if(data.status === 'success'){
			config.ondone(data,file_index,file,file_count,this);
			if(file_count === file_index){
				all_complete = true;
				is_uploading = false;
				config.$file.val('');
			}else{
				upload_next(files[file_index]);
			}
		}else{
			if(file_count > file_index){
				upload_next(files[file_index]);
			}else{
				config.onerror(data,file_index,file,file_count);
				config.$file.val('');

			}
		}
		config.onalways(data,file_index,file,file_count,this);

	}
	function upload_next(file){
		var end_time = new Date(),
		interval_time = end_time - start_time,
		timeout = config.interval - interval_time,
		timeout = timeout < 0 ? 0 :timeout;
		setTimeout(function(){
			file_upload(next_file);
		},timeout);
	}
}

	
});
