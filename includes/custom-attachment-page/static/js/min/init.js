
define(function(require,exports,module){'use strict';var tools=require('modules/tools');exports.init=function(){tools.ready(exports.bind);}
var cache={};exports.bind=function(){cache.$thumbnail_container=document.querySelector('.attachment-slide-thumbnail');if(!cache.$thumbnail_container)
return false;cache.$thumbnails=cache.$thumbnail_container.querySelectorAll('a');if(cache.$thumbnails.length<=1)
return false;cache.$thumbnail_active=cache.$thumbnail_container.querySelector('a.active');cache.$thumbnail_container.scrollLeft=cache.$thumbnail_active.offsetLeft/2;}});