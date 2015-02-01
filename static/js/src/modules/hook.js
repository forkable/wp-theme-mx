/* js action filter */
define(function(require, exports, module){

	module.exports = (function(ns){
	
		var filters = {};
		var max_priority = 10;
		var default_priority = 10;
	 
		var each = function(a, cb) {
			a = a.concat([]);
			for (var i = 0, j = a.length; i < j; i ++) {
				if (cb && cb(i, a[i]) === false) break;
			}
		};
	 
		var array_include = function(a, v) {
			for (var i = 0, j = a.length; i < j; i ++)
				if (a[i] === v) return true;
	 
			return false;
		};
	 
		var to_array = function(a) {
			var rt = [];
			for (var i = 0, j = a.length; i < j; i ++) {
			   rt.push(a[i]); 
			}
			return rt;
		};
	 
		function _set(func, tag, priority, key, value) {
			func['__filter_' + tag + '_' + priority + '_' + key] = value; 
		}
	 
		function _get(func, tag, priority, key) {
			return func['__filter_' + tag + '_' + priority + '_' + key]; 
		}
	 
		ns.add_filter = ns.add_action = function(tag, func, priority, run_once) {
			run_once = typeof run_once === 'undefined' ? false : run_once;
			priority = priority || default_priority; 
			each(tag.split('|'), function(i, tag) {
				if (!filters[tag]) filters[tag] = {};   
				if (!filters[tag][priority]) filters[tag][priority] = [];
				if (!array_include(filters[tag][priority], func)) {
					filters[tag][priority].push(func);
				}
	 
				_set(func, tag, priority, 'run_once', run_once); 
			});
		};
	 
		ns.remove_filter = ns.remove_action = function(tag, func, priority, run_once) {
			priority = priority || default_priority;
			each(tag.split('|'), function(i, tag) {
				if (!filters[tag]) return;
				if (!filters[tag][priority]) return;
				var funcs = filters[tag][priority]; 
				each(funcs, function(i, f) {
				   if (f === func)
						funcs.splice(i, 1);
				});
			});
		};
	 
		ns.add_once_filter = ns.add_once_action = function(tag, func, priority) {
			ns.add_filter(tag, func, priority, true);
		};
	 
		function filter_or_action(ac, tag, value) {
			if (!filters[tag]) return value;
	 
			var args = to_array(arguments);
			args.shift();args.shift();
	 
			if (ac === 'filter') args.shift();
	 
			var _cfs = filters[tag];
			var i = 1, func, funcs; 
			var rt = value;
			var breaked = false;
			while ( i <= max_priority ) {
				funcs = _cfs[i];
				if ( !funcs || funcs.length === 0 ) {
					i ++;
					continue;
				}
	 
				each(funcs, function(i, func) {
					if (typeof func !== 'function') {
						return;
					}
	 
					if (ac == 'action') {
						rt = func.apply(null, args);
					} else {
						rt = func.apply(null, [rt].concat(args));
					}
	 
					if (_get(func, tag, i, 'run_once')) {
						funcs.splice(i, 1);
					} 
	 
					if ( rt && rt['end'] ) {
						rt = rt['value'] || null;
						breaked = true;
						return false;
					}
				});
	 
				if (breaked) return rt;
				i ++;
			}
			return rt;
		};
	 
		ns.apply_filters = function(tag, value) {
			var args = to_array(arguments);
			return filter_or_action.apply(null, ['filter'].concat(args));
		};
	 
		ns.do_action = function(tag) {
			var args = to_array(arguments);
			filter_or_action.apply(null, ['action'].concat(args));
		};
	})(window);
});