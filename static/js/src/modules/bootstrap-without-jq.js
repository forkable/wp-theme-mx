/*!
 * Bootstrap without jQuery v0.6.1 for Bootstrap 3
 * By Daniel Davis under MIT License
 * https://github.com/tagawa/bootstrap-without-jquery
 */

define(function(require,exports,module){
    'use strict';
    
    /*
     * Collapse action
     * 1. Get list of all elements that are collapse triggers
     * 2. Add click event listener to these elements
     * 3. When clicked, change target element's class name from "collapse" to "collapsing"
     * 4. When action (collapse) is complete, change target element's class name from "collapsing" to "collapse in"
     * 5. Do the reverse, i.e. "collapse in" -> "collapsing" -> "collapse"
     */
     
	// Get an event's target element and the element specified by the "data-target" attribute
	function getTargets(event) {
	    var targets = {};
	    event = event || window.event;
	    targets.evTarget = event.currentTarget || event.srcElement;
	    var dataTarget = targets.evTarget.getAttribute('data-target');
	    targets.dataTarget = (dataTarget) ? document.querySelector(dataTarget) : false;
	    return targets;
	}
    // Show a target element
    function show(element, trigger) {
	    
        trigger.classList.add('collapsed');
        element.classList.add('in');
        //trigger.classList.remove('collapse');
        element.setAttribute('aria-expanded', true);
    }
    
    // Hide a target element
    function hide(element, trigger) {
        trigger.classList.add('collapsed');
        element.classList.remove('in');
        element.classList.add('collapse');
        element.setAttribute('aria-expanded', false);
    }
    

    // Start the collapse action on the chosen element
    function doCollapse(event) {
        event.preventDefault();
        var targets = getTargets(event);
        var dataTarget = targets.dataTarget;
        
        // Add the "in" class name when elements are unhidden
        if (dataTarget.classList.contains('in')) {
            hide(dataTarget, targets.evTarget);
        } else {
            show(dataTarget, targets.evTarget);
        }
        return false;
    }
    
    // Get all elements that are collapse triggers and add click event listeners
    var collapsibleList = document.querySelectorAll('[data-toggle=collapse]');
    for (var i = 0, leni = collapsibleList.length; i < leni; i++) {
        collapsibleList[i].onclick = doCollapse;
    }
    
    /*
     * Dropdown action
     * 1. Get list of all elements that are dropdown triggers
     * 2. Add click and blur event listeners to these elements
     * 3. When clicked, add "open" to the target element's class names, or remove if it exists
     * 4. On blur, remove "open" from the target element's class names
     */
     
    // Show a dropdown menu
    function doDropdown(event) {
        event = event || window.event;
        var evTarget = event.currentTarget || event.srcElement;
        evTarget.parentElement.classList.toggle('open');
        return false;
    }
    
    // Close a dropdown menu
    function closeDropdown(event) {
	    //setTimeout(function(){
	        event = event || window.event;
	        var evTarget = event.currentTarget || event.srcElement;
	        //evTarget.parentElement.classList.remove('open');
	        
	        // Trigger the click event on the target if it not opening another menu
	        if(event.relatedTarget && event.relatedTarget.getAttribute('data-toggle') !== 'dropdown') {
	            //event.relatedTarget.click();
	        }			    
	    //},100);

        return false;
    }
    
    // Set event listeners for dropdown menus
    var dropdownList = document.querySelectorAll('[data-toggle=dropdown]');
    for (var k = 0, dropdown, lenk = dropdownList.length; k < lenk; k++) {
        dropdown = dropdownList[k];
        dropdown.setAttribute('tabindex', '0'); // Fix to make onblur work in Chrome
        dropdown.onclick = doDropdown;
        dropdown.onblur = closeDropdown;
    }
});
