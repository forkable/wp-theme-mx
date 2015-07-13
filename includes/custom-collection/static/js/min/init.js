
define(function(require,exports,module){'use strict';var tools=require('modules/tools'),js_request=require('theme-cache-request');exports.config={process_url:'',tpl_input:'',tpl_preview:'',min_posts:5,max_posts:10,lang:{M01:'Loading, please wait...',M02:'A item has been deleted.',M03:'Getting post data, please wait...',M04:'Previewing, please wait...',E01:'Sorry, server is busy now, can not respond your request, please try again later.',E02:'Sorry, the minimum number of posts is %d.',E03:'Sorry, the maximum number of posts is %d.',E04:'Sorry, the post id must be number, please correct it.'}}
var cache={},config=exports.config;exports.init=function(){upload();list();preview();post();}
function get_posts_count(){return document.querySelectorAll('.clt-list').length;}
function preview(){var $preview=I('clt-preview');cache.$preview_container=I('clt-preview-container');if(!$preview)
return false;$preview.addEventListener('click',function(){var lists_count=get_posts_count();if(lists_count<config.min_posts){tools.ajax_loading_tip('error',config.lang.E02,3);return false;}else if(lists_count>config.max_posts){tools.ajax_loading_tip('error',config.lang.E03,3);return false;}
show_preview();return false;},false);function show_preview(){var $lists=document.querySelectorAll('.clt-list'),tpl='';for(var i=0,len=$lists.length;i<len;i++){var $requires=$lists[i].querySelectorAll('[required]');for(var j=0,l=$requires.length;j<l;j++){if($requires[j].value.trim()===''){tools.ajax_loading_tip('error',$requires[j].getAttribute('title'),3);$requires[j].focus();return false;}}
var id=$lists[i].getAttribute('data-id'),$imgs=$lists[i].querySelectorAll('img'),thumbnail_url=$imgs[$imgs.length-1].src;tpl+=tools.replace_array(config.tpl_preview,['%hash%','%title%','%thumbnail%','%content%'],[id,I('clt-list-post-title-'+id).value,thumbnail_url,I('clt-list-post-content-'+id).value]);}
preview_done(tpl);}
function preview_done(tpl){cache.$preview_container.innerHTML=tpl;}}
function list(){var _cache={},$lists=document.querySelectorAll('.clt-list');if(!$lists[0])
return false;_cache.$add=I('clt-add-post');_cache.$container=I('clt-lists-container');for(var i=0,len=$lists.length;i<len;i++){bind_list($lists[i]);}
add_list();function add_list(){var helper=function(){if(get_posts_count()>=config.max_posts){tools.ajax_loading_tip('error',config.lang.E03,3);return false;}
var rand=Date.now(),$tmp=document.createElement('div'),$new_list;$tmp.innerHTML=get_input_tpl(rand);$new_list=$tmp.firstChild;$new_list.classList.add('delete');_cache.$container.appendChild($new_list);bind_list($new_list);setTimeout(function(){$new_list.classList.remove('delete');},1);return false;};_cache.$add.addEventListener('click',helper,false);}
function get_input_tpl(placeholder){return config.tpl_input.replace(/%placeholder%/g,placeholder);}
function bind_list($list){if(!$list)
return false;var placeholder=$list.getAttribute('data-id');del(placeholder);show_post(placeholder);function del(placeholder){var helper=function(){if(get_posts_count()<=config.min_posts){tools.ajax_loading_tip('error',config.lang.E02,3);return false;}
$list.classList.add('delete');setTimeout(function(){$list.parentNode.removeChild($list);},500);return false;};I('clt-list-del-'+placeholder).addEventListener('click',helper,false);;}
function show_post(placeholder){post_id_blur();function post_id_blur(){var $post_id=I('clt-list-post-id-'+placeholder),helper=function(evt){evt.preventDefault();var post_id=this.value;if(post_id.trim()==='')
return false;if(isNaN(post_id.trim())===true){this.select();tools.ajax_loading_tip('error',config.lang.E04,3);return false;}
if(!get_post_cache_data(post_id)){ajax(post_id,placeholder,this);}else{set_post_data(post_id,placeholder);}}
$post_id.addEventListener('change',helper,false);$post_id.addEventListener('blur',helper,false);}
function ajax(post_id,placeholder,$post_id){tools.ajax_loading_tip('loading',config.lang.M03);var xhr=new XMLHttpRequest(),ajax_data={'type':'get-post','post-id':post_id,'theme-nonce':js_request['theme-nonce']};xhr.open('GET',config.process_url+'&'+tools.param(ajax_data));xhr.send();xhr.onload=function(){if(xhr.status>=200&&xhr.status<400){var data;try{data=JSON.parse(xhr.responseText)}catch(err){data=xhr.responseText}
done(data);}else{tools.ajax_loading_tip('error',config.lang.E01);}};xhr.onerror=function(){tools.ajax_loading_tip('error',config.lang.E01);};function done(data){if(data&&data.status==='success'){set_post_cache(post_id,data);set_post_data(post_id,placeholder);tools.ajax_loading_tip(data.status,data.msg,3);}else if(data&&data.status==='error'){set_post_cache(post_id,data);$post_id.select();tools.ajax_loading_tip(data.status,data.msg,3);}else{tools.ajax_loading_tip('error',data);}}}
function set_post_cache(post_id,data){if(cache.posts&&cache.posts[post_id])
return false;if(!cache.posts)
cache.posts={};cache.posts[post_id]={'thumbnail':data.thumbnail,'title':data.title,'excerpt':data.excerpt};}
function get_post_cache_data(post_id,key){if(!cache.posts||!cache.posts[post_id])
return false;if(!key)
return cache.posts[post_id];return cache.posts[post_id][key];}
function set_post_data(post_id,placeholder){var $content=I('clt-list-post-content-'+placeholder),$thumbnail=I('clt-list-thumbnail-'+placeholder),$thumbnail_url=I('clt-list-thumbnail-url-'+placeholder);if(cache.posts[post_id].title)
I('clt-list-post-title-'+placeholder).value=cache.posts[post_id].title;if(cache.posts[post_id].excerpt&&$content.value.trim()==='')
$content.value=cache.posts[post_id].excerpt;if(cache.posts[post_id].thumbnail){$thumbnail.src=cache.posts[post_id].thumbnail.url;$thumbnail_url.value=cache.posts[post_id].thumbnail.url;}}}}}
function post(){var _cache={};_cache.$fm=I('fm-clt');if(!_cache.$fm)
return false;var sm=new tools.validate();sm.$fm=_cache.$fm;sm.process_url=config.process_url;sm.error_tx=config.lang.E01;sm.init();}
function upload(){var $file=I('clt-file'),$progress_bar=I('clt-file-progress'),$complete=I('clt-file-completion'),$files=I('clt-files'),_cache={};_cache.$cover=I('clt-cover');_cache.$progress=I('clt-file-progress');_cache.$tip=I('clt-file-tip');_cache.$progress_bar=I('clt-file-progress-bar');_cache.$progress_tx=I('clt-file-progress-tx');_cache.$thumbnail_id=I('clt-thumbnail-id');_cache.$file_area=I('clt-file-area');if(!$file)
return false;$file.addEventListener('change',file_select);$file.addEventListener('drop',file_drop);$file.addEventListener('dragover',file_select);function dragover(evt){evt.stopPropagation();evt.preventDefault();evt.dataTransfer.dropEffect='copy';}
function file_drop(e){e.stopPropagation();e.preventDefault();_cache.files=e.dataTransfer.files;file_upload(_cache.files[0]);}
function file_select(e){e.stopPropagation();e.preventDefault();_cache.files=e.target.files.length?e.target.files:e.originalEvent.dataTransfer.files;file_upload(_cache.files[0]);}
function file_upload(file){var reader=new FileReader();reader.onload=function(e){submission(file);};reader.readAsDataURL(file);}
function submission(file){progress_tip('loading',config.lang.M01);var fd=new FormData(),xhr=new XMLHttpRequest();fd.append('type','add-cover');fd.append('theme-nonce',js_request['theme-nonce']);fd.append('img',file);xhr.open('post',config.process_url);xhr.send(fd);xhr.upload.onprogress=function(e){if(e.lengthComputable){var percent=e.loaded/e.total*100;_cache.$progress_bar.style.width=percent+'%';}};xhr.onload=function(){if(xhr.status>=200&&xhr.status<400){var data;try{data=JSON.parse(xhr.responseText)}catch(err){data=xhr.responseText}
if(data&&data.status==='success'){_cache.$cover.src=data.thumbnail.url;_cache.$thumbnail_id.value=data['attach-id'];tools.ajax_loading_tip(data.status,data.msg,3);}else if(data&&data.status==='error'){tools.ajax_loading_tip(data.status,data.msg);}else{tools.ajax_loading_tip('error',data);}}else{tools.ajax_loading_tip('error',config.lang.E01);}
progress_tip('hide');};xhr.onerror=function(){tools.ajax_loading_tip('error',config.lang.E01);};}
function progress_tip(t,s){if(t==='hide'){_cache.$progress.style.display='none';_cache.$file_area.style.display='block';return false;}
_cache.$file_area.style.display='none'
_cache.$progress.style.display='block';_cache.$progress_bar.style.width='10%';_cache.$progress_tx.innerHTML=tools.status_tip(t,s);}}
function I(e){return document.getElementById(e);}});