
define(function(require,exports,module){'use strict';function getTargets(event){var targets={};event=event||window.event;targets.evTarget=event.currentTarget||event.srcElement;var dataTarget=targets.evTarget.getAttribute('data-target');targets.dataTarget=(dataTarget)?document.querySelector(dataTarget):false;return targets;}
function show(element,trigger){trigger.classList.add('collapsed');element.classList.add('in');element.setAttribute('aria-expanded',true);}
function hide(element,trigger){trigger.classList.add('collapsed');element.classList.remove('in');element.classList.add('collapse');element.setAttribute('aria-expanded',false);}
function doCollapse(event){event.preventDefault();var targets=getTargets(event);var dataTarget=targets.dataTarget;if(dataTarget.classList.contains('in')){hide(dataTarget,targets.evTarget);}else{show(dataTarget,targets.evTarget);}
return false;}
var collapsibleList=document.querySelectorAll('[data-toggle=collapse]');for(var i=0,leni=collapsibleList.length;i<leni;i++){collapsibleList[i].onclick=doCollapse;}
function doDropdown(event){event=event||window.event;var evTarget=event.currentTarget||event.srcElement;evTarget.parentElement.classList.toggle('open');return false;}
function closeDropdown(event){event=event||window.event;var evTarget=event.currentTarget||event.srcElement;if(event.relatedTarget&&event.relatedTarget.getAttribute('data-toggle')!=='dropdown'){}
return false;}
var dropdownList=document.querySelectorAll('[data-toggle=dropdown]');for(var k=0,dropdown,lenk=dropdownList.length;k<lenk;k++){dropdown=dropdownList[k];dropdown.setAttribute('tabindex','0');dropdown.onclick=doDropdown;dropdown.onblur=closeDropdown;}});