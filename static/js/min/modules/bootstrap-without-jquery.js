/*!
 * Bootstrap without jQuery v0.6.1 for Bootstrap 3
 * By Daniel Davis under MIT License
 * https://github.com/tagawa/bootstrap-without-jquery
 */
(function(){'use strict';function transitionEndEventName(){var i,el=document.createElement('div'),transitions={'transition':'transitionend','OTransition':'otransitionend','MozTransition':'transitionend','WebkitTransition':'webkitTransitionEnd'};for(i in transitions){if(transitions.hasOwnProperty(i)&&el.style[i]!==undefined){return transitions[i];}}
return false;}
var transitionend=transitionEndEventName();function getTargets(event){var targets={};event=event||window.event;targets.evTarget=event.currentTarget||event.srcElement;var dataTarget=targets.evTarget.getAttribute('data-target');targets.dataTarget=(dataTarget)?document.querySelector(dataTarget):false;return targets;}
function getMaxHeight(element){var prevHeight=element.style.height;element.style.height='auto';var maxHeight=getComputedStyle(element).height;element.style.height=prevHeight;element.offsetHeight;return maxHeight;}
function fireTrigger(element,eventType){if(document.createEvent){var event=document.createEvent('HTMLEvents');event.initEvent(eventType,true,false);element.dispatchEvent(event);}else{element.fireEvent('on'+eventType);}}
function show(element,trigger){element.classList.remove('collapse');element.classList.add('collapsing');trigger.classList.remove('collapsed');trigger.setAttribute('aria-expanded',true);element.style.height=getMaxHeight(element);if(transitionend){element.addEventListener(transitionend,function(){complete(element);},false);}else{complete(element);}}
function hide(element,trigger){element.classList.remove('collapse');element.classList.remove('in');element.classList.add('collapsing');trigger.classList.add('collapsed');trigger.setAttribute('aria-expanded',false);element.style.height=getComputedStyle(element).height;element.offsetHeight;element.style.height='0px';}
function complete(element){element.classList.remove('collapsing');element.classList.add('collapse');element.setAttribute('aria-expanded',false);if(element.style.height!=='0px'){element.classList.add('in');element.style.height='auto';}}
function doCollapse(event){event.preventDefault();var targets=getTargets(event);var dataTarget=targets.dataTarget;if(dataTarget.classList.contains('in')){hide(dataTarget,targets.evTarget);}else{show(dataTarget,targets.evTarget);}
return false;}
var collapsibleList=document.querySelectorAll('[data-toggle=collapse]');for(var i=0,leni=collapsibleList.length;i<leni;i++){collapsibleList[i].onclick=doCollapse;}
function doDismiss(event){event.preventDefault();var targets=getTargets(event);var target=targets.dataTarget;if(!target){var parent=targets.evTarget.parentNode;if(parent.classList.contains('alert')){target=parent;}else if(parent.parentNode.classList.contains('alert')){target=parent.parentNode;}}
fireTrigger(target,'close.bs.alert');target.classList.remove('in');function removeElement(){try{target.parentNode.removeChild(target);fireTrigger(target,'closed.bs.alert');}catch(e){window.console.error('Unable to remove alert');}}
if(transitionend&&target.classList.contains('fade')){target.addEventListener(transitionend,function(){removeElement();},false);}else{removeElement();}
return false;}
var dismissList=document.querySelectorAll('[data-dismiss=alert]');for(var j=0,lenj=dismissList.length;j<lenj;j++){dismissList[j].onclick=doDismiss;}
function doDropdown(event){event=event||window.event;var evTarget=event.currentTarget||event.srcElement;evTarget.parentElement.classList.toggle('open');return false;}
function closeDropdown(event){event=event||window.event;var evTarget=event.currentTarget||event.srcElement;evTarget.parentElement.classList.remove('open');if(event.relatedTarget&&event.relatedTarget.getAttribute('data-toggle')!=='dropdown'){event.relatedTarget.click();}
return false;}
var dropdownList=document.querySelectorAll('[data-toggle=dropdown]');for(var k=0,dropdown,lenk=dropdownList.length;k<lenk;k++){dropdown=dropdownList[k];dropdown.setAttribute('tabindex','0');dropdown.onclick=doDropdown;dropdown.onblur=closeDropdown;}
function tabpanel(){function forEachElement(els,fn){for(var i=0,l=els.length;i<l;i++)
fn(els[i],i);}
var tablists=document.querySelectorAll('[role=tablist]');function action(e){e.preventDefault();var a=this,targetTabContent=document.getElementById(a.getAttribute('aria-controls')),targetParent=targetTabContent.parentNode,siblings=targetParent.parentNode.querySelectorAll('[data-toggle=tab]'),childTargetContents=targetParent.querySelectorAll('.tab-pane');forEachElement(siblings,function(el,i){if(el.getAttribute('aria-controls')==a.getAttribute('aria-controls')){el.parentNode.classList.add('active');}else{el.parentNode.classList.remove('active');}});forEachElement(childTargetContents,function(el,i){if(el.id==targetTabContent.id){el.classList.add('active');}else{el.classList.remove('active');}});}
forEachElement(tablists,function(tablist,i){forEachElement(tablist.querySelectorAll('[data-toggle=tab]'),function(a,k){a.addEventListener('click',action,false);});});}
tabpanel();})();