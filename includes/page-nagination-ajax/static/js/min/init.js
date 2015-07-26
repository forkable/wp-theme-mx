
define(function(require,exports,module){'use strict';var tools=require('modules/tools');exports.config={process_url:'',post_id:'',numpages:'',url_tpl:'',page:1,lang:{M01:'Loading, please wait...',M02:'Content loaded.',M03:'Already first page.',M04:'Already last page.',E01:'Sorry, some server error occurred, the operation can not be completed, please try again later.'}};var cache={},config=exports.config;exports.init=function(){tools.ready(function(){exports.page_nagi.init();exports.pagi_ajax();});}
exports.page_nagi={init:function(){var that=this;cache.$post=document.querySelector('.singluar-post');cache.$nagi=document.querySelector('.page-pagination');cache.$next=cache.$nagi.querySelector('.next');cache.$prev=cache.$nagi.querySelector('.prev');cache.$next_number=cache.$next.querySelector('.current-page');cache.$prev_number=cache.$prev.querySelector('.current-page');if(!cache.$post||!cache.$nagi)
return;cache.post_top;cache.max_bottom;cache.is_hide=false;window.addEventListener('resize',function(){that.reset_nagi_style()});cache.$nagi.style.display='block';this.bind();},bind:function(rebind){if(rebind===true){cache.$nagi=document.querySelector('.page-pagination');}
this.reset_nagi_style();},reset_nagi_style:function(){cache.post_top=this.getElementTop(cache.$post);cache.max_bottom=cache.post_top+cache.$post.querySelector('.panel-body').clientHeight;cache.$nagi.style.left=this.getElementLeft(cache.$post)+'px';cache.$nagi.style.width=cache.$post.clientWidth+'px';},getElementLeft:function(e){var l=e.offsetLeft,c=e.offsetParent;while(c!==null){l+=c.offsetLeft;c=c.offsetParent;}
return l;},getElementTop:function(e){var l=e.offsetTop,c=e.offsetParent;while(c!==null){l+=c.offsetTop;c=c.offsetParent;}
return l;}};exports.pagi_ajax=function(){if(!cache.$nagi)
return;cache.$post_content=document.querySelector('.post-content');cache.$as=cache.$nagi.querySelectorAll('a');for(var i=0,len=cache.$as.length;i<len;i++){cache.$as[i].addEventListener('click',event_click);}
set_cache(config.page,cache.$post_content.innerHTML);function get_data_from_cache(id){if(!cache.post_contents||!cache.post_contents[id])
return false;return cache.post_contents[id];}
function set_cache(id,data){if(!cache.post_contents)
cache.post_contents=[];cache.post_contents[id]=data;}
function set_post_content(content){cache.$post_content.innerHTML=content;}
function get_next_page(){return cache.$current==cache.$next?config.page+1:config.page-1;}
function event_click(e){e.preventDefault();cache.$current=this;if(is_first_page()){tools.ajax_loading_tip('warning',config.lang.M03,3);return false;}
if(is_last_page()){tools.ajax_loading_tip('warning',config.lang.M04,3);return false;}
if(get_data_from_cache(get_next_page())){set_post_content(get_data_from_cache(get_next_page()));pagenumber();hash();return;}
tools.ajax_loading_tip('loading',config.lang.M01);var xhr=new XMLHttpRequest();xhr.open('get',config.process_url+'&page='+get_next_page());xhr.send();xhr.onload=function(){if(xhr.status>=200&&xhr.status<400){var data;try{data=JSON.parse(xhr.responseText);}catch(e){data=xhr.responseText}
if(data&&data.status){done(data);}else{fail(data);}}else{fail();}};xhr.onerror=function(){fail();};}
function done(data){if(data.status==='success'){set_cache(get_next_page(),data.content)
set_post_content(data.content);pagenumber();hash();tools.ajax_loading_tip('hide');}else if(data.status==='error'){tools.ajax_loading_tip(data.status,data.msg);}}
function fail(data){if(data){tools.ajax_loading_tip('error',data);}else{tools.ajax_loading_tip('error',config.lang.E01);}}
function hash(){var url=config.url_tpl.replace(9999,config.page);history.replaceState(null,null,url);location.hash='';location.hash='#'+cache.$post.id;}
function pagenumber(){config.page=get_next_page();cache.$next_number.innerHTML=config.page;cache.$prev_number.innerHTML=config.page;}
function is_first_page(){return cache.$current==cache.$prev&&config.page==1;}
function is_last_page(){return cache.$current==cache.$next&&config.page==config.numpages;}}});