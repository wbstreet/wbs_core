/*
Author: Polyakov Konstantin
Licence: Domain Public

You can use this code for everything! But be very carefull :)
*/

function Request(method, url, post, async_func) {
	if (url === undefined) url = WB_URL+'/api.php'; 

	post = post || '';
	async_func = async_func || null;

	if (async_func === null) {var is_async = false;} else {var is_async = true;}
	
    var req = new XMLHttpRequest();
    req.open(method, url, is_async);
    req.send(post);

    if (is_async) {req.onreadystatechange = async_func;}
    return req;
}

function RequestAction(action_name, url, arr, async_func) {
	async_func = async_func || null;
    var form = new FormData();
    form.append('action', action_name);
    for (var name in arr) {
        if (!arr.hasOwnProperty(name)) {continue;}
        if (arr[name] instanceof FileList || arr[name] instanceof Array) {
            for(var i=0; i < arr[name].length; i++) form.append(name, arr[name][i]);
        }else {form.append(name, arr[name]);}
    }

    return Request('post', url, form, async_func)
}
function del_casper(text) {
	// удаляем касперского, а адекватное другое решение позже реализую
	return text.replace(/<script type="text\/javascript" src="http:\/\/gc\.kis\.v2\.scr\.kaspersky-labs\.com\/[-A-Z0-9]+\/main\.js" charset="UTF-8"><\/script>/, '');
}

function RA_raw(action, data, options) {
    options['func_after'] = options['func_after'] || options['func_after_load'];
    
	RequestAction(action, options['url'], data, function() {
		if (this.readyState != 4) return;
        if (this.status==200) {
	        var res = JSON.parse(del_casper(this.responseText));
        	if (options['func_after']) options['func_after'](res);
	        if (res['success'] == 1) {
	        	if (options['func_success']) options['func_success'](res, options['arg_func_success']);
	        } else {
	        	if (options['func_error']) options['func_error'](res);
	        }
	    } else if (!navigator.onLine) {
        	if (options['func_fatal']) options['func_fatal']('Нет соединения с Интернет');
        } else {
        	if (options['func_fatal']) options['func_fatal']('Неизветсная ошибка');
        }
        // повторяется в admin/cabinet/tab_page/index.js
        if (window.grecaptcha && data['grecaptcha_widget_id']) grecaptcha.reset(data['grecaptcha_widget_id']); // сбрасываем капчу гугла
	})
}

/*function Request(method, url, post, async_func) {
	post = post || '';
	async_func = async_func || null;

	if (async_func === null) {var is_async = false;} else {var is_async = true;}
	
    var req = new XMLHttpRequest();
    req.open(method, url, is_async);
    req.send(post);

    if (is_async) {req.onreadystatechange = async_func;}
    return req;
}

function RequestAction(action_name, arr, async_func) {
	async_func = async_func || null;
    var form = new FormData();
    form.append('action', action_name);
    for (var name in arr) {
        if (!arr.hasOwnProperty(name)) {continue;}
        if (arr[name] instanceof FileList || arr[name] instanceof Array) {
            for(var i=0; i < arr[name].length; i++) form.append(name, arr[name][i]);
        }else {form.append(name, arr[name]);}
    }

    return Request('post', WB_URL+'/api.php', form, async_func)
}

function del_casper(text) {
	// удаляем касперского, а адекватное другое решение позже реализую
	return text.replace(/<script type="text\/javascript" src="http:\/\/gc\.kis\.v2\.scr\.kaspersky-labs\.com\/[-A-Z0-9]+\/main\.js" charset="UTF-8"><\/script>/, '');
}

function RA_raw(action, data, options) {
	RequestAction(action, data, function() {
		if (this.readyState != 4) return;
        if (this.status==200) {
	        var res = JSON.parse(del_casper(this.responseText));
        	if (options['func_after_load']) options['func_after_load'](res);
	        if (res['success'] == 1) {
	        	if (options['func_success']) options['func_success'](res);
	        } else {
	        	if (options['func_error']) options['func_error'](res);
	        }
	    } else if (!navigator.onLine) {
        	if (options['func_fatal']) options['func_fatal']('Нет соединения с Интернет');
        } else {
        	if (options['func_fatal']) options['func_fatal']('Неизветсная ошибка');
        }
        // повторяется в admin/cabinet/tab_page/index.js
        if (window.grecaptcha && data['grecaptcha_widget_id']) grecaptcha.reset(data['grecaptcha_widget_id']); // сбрасываем капчу гугла
	})
}*/

function show_button_message(button, message, timeout) {
	var process;
    if (button.nextSibling == null || button.nextSibling.className != 'RA_ButtonProgress') {
        process = document.createElement('span');
        process.style.marginLeft = '10px';
        process.className = 'RA_ButtonProgress';
        button.parentElement.insertBefore(process, button.nextSibling);
	} else {process = button.nextSibling;}
	//process.textContent = message;
	process.innerHTML = message;
	if (timeout) setTimeout(function() {process.remove();}, timeout);
}

function animate_element(el, name) {
   	el.classList.add(name);
   	setTimeout(function() {el.classList.remove(name);}, 600);
}

function light_absent_fields(form, absent_fields) {
	var i, field;
/*	for (i = 0; i<absent_fields.length; i++) {
		field = absent_fields[i];
		field = form.elements[field];
		if (field !== undefined) field.style.border = border;
	}*/
	for (i = 0; i<form.elements.length; i++) {
		field = form.elements[i];
		if (absent_fields.indexOf(field.name) != -1) {field.style.border = '1px solid red'; field.style.background = '#ffe6e6';}
		else {
			if (field.type == 'button') continue;
			field.style.border = '1px solid green'; field.style.background = '#e6ffe6';
		}
	}
}

function RA_ButtonProgress(action, data, button, sending_text, func_success, options) {
	sending_text = sending_text || "Отправляется...";
	show_button_message(button, sending_text)
	options = options || [];

    RA_raw(action, data, {
    	func_success: function(res) {
         	var timeout = res['timeout'] !== undefined ? res['timeout'] : 3000 ;
        	show_button_message(button,  res['message'], timeout);
        	if (func_success) func_success(res, options['arg_func_success']);
    	},
    	func_error: function(res) {
        	show_button_message(button, 'ошибка: '+res['message']);
        	animate_element(button, 'btn-err')
        	if (res['absent_fields'] !== undefined) light_absent_fields(button.form, res['absent_fields']);
    	},
    	func_fatal: function(res) {
        	show_button_message(button, 'неизвестная ошибка(');
    	},
    	url: options['url'],
    	func_after: options['func_after']
    })
}

function showNotification(message, _type, time) {
	time = time || 7000;
	var notes = document.getElementById('notifications');
    if (!notes) {
        notes = document.createElement('div'); notes.id = 'notifications';
        notes.style.position = "fixed";
        notes.style.top = "15px";
        notes.style.right = "15px";
        document.body.appendChild(notes);
    }
    notification_colors = {'note': '#3f3', 'error':'#f55'};

    var note = document.createElement('div');
    note.style.color = notification_colors[_type];
    note.style.background = "#222";
    note.style.padding = "20px";
    note.style.marginBottom = "10px";
    note.className = 'notification';
    note.innerHTML = message;//note.appendChild(document.createTextNode(message));
    notes.appendChild(note);
    zi.add(notes, 'top');
    setTimeout(function(){zi.remove(note);note.remove();}, time);
}

function RA_Notification(action, data, func_success, options) {
    RA_raw(action, data, {
    	func_success: function(res) {
         	var timeout = res['timeout'] !== undefined ? res['timeout'] : 3000 ;
        	showNotification(res['message'], 'note', timeout);
        	if (func_success) func_success(res, options['arg_func_success']);
    	},
    	func_error: function(res) {
         	var timeout = res['timeout'] !== undefined ? res['timeout'] : 7000 ;
        	showNotification('ошибка сервера: '+res['message'], 'error', timeout);
    	},
    	func_fatal: function(res) {
        	showNotification('неизвестная ошибка(', 'error');
    	},
    	url: options['url'],
    	func_after: options['func_after']
    })
}

// for checkboxes and [radio]
function set_checkbox(checkboxes, values) {
	// form = document.forms['имя формы']; checkboxes = form['имя флажков'];
	for (var i=0; i< checkboxes.length; i++ ) {
		var cb = checkboxes[i];
    	if (values.indexOf(cb.value) == -1) continue;
    	cb.checked = true;
	}
}

function get_checkbox(checkboxes) {
	var checkboxes_arr = [];
	for (var i=0; i< checkboxes.length; i++ ) {
		var cb = checkboxes[i];
		if (!cb.checked) continue;
		checkboxes_arr[checkboxes_arr.length] = cb.value;
    }
    return checkboxes_arr;
}

function FormTools() {}

process_form_fields = {
	'edit_page_visitcard': {
		'visitcard': {
			'fromForm': {
				'transformValue': function(origin_value) {
		            return document.getElementById('edit_page_visitcard').src;
				},
				// возвращает undefined или true в случае успеха.
				'filterAfterTransform': function (value) {
	                if (value == '') return 'Выберите визитку!';
		            if (value.length > 1024*150) return 'Визитка должна быть < 100 Кб ! Ваш размер: '+(value.length/1024);
				}
			}
		}
	}
};

function proccess_value(value, name, form, direction) {
	var ret = [value, undefined];

   	if (form.name === undefined) return ret;
	if (process_form_fields[form.name] === undefined) return ret;
	if (process_form_fields[form.name][name] === undefined) return ret;
	if (process_form_fields[form.name][name][direction] === undefined) return ret;
	var pff = process_form_fields[form.name][name][direction];

    var results = [];

    if (direction == 'fromForm') {
    
		if (pff['filterBeforeTransform'] !== undefined) {
			var result1 = pff['filterBeforeTransform'](value);
			if (result1 !== undefined || result1 !== true) return [value, result1];
		}
		if (pff['transformValue'] !== undefined) value = pff['transformValue'](value);
		if (pff['filterAfterTransform']  !== undefined) {
			var result2 = pff['filterAfterTransform'](value);
	    	if (result2 !== undefined || result2 !== true) return [value, result2];
		}

    }
	
	return [value, undefined];
	
}

// можно передавать массивы в качестве значения
function get_form_fields(form, ignore_fields, use_filter) {
	var el,
	    value,
	    data = {},
	    ret;
	ignore_fields = ignore_fields || [];
	use_filter = use_filter || false;

	for (var i = 0; i< form.elements.length; i+=1) {
		el = form.elements[i];
   		if (el.name === undefined || el.name === '' || ignore_fields.indexOf(el.name) != -1) continue;

        if (form[el.name].tagName !== undefined) { // если это элемент
    		if (el.type == 'checkbox' || el.type == 'radio') value = el.checked;//if (el.hasOwnProperty('checked')) value = el.checked;
   			else if (el.type == 'file') value = el.files;
   			else value = el.value;

            // фильтрация и преобразования значения
            if (use_filter) {
	    		ret = proccess_value(value, el.name, form, 'fromForm')
	    		if (ret[1] == undefined || ret[1] == true) value = ret[0];
	    		else return {'data': data, 'is_error':true,  'error': ret[1]};
            }

        } else { // если это коллекция элементов с одинаковым 'name'
   			value = [];
 			for (var j=0; j < form[el.name].length; j++ ) {
				var _el = form[el.name][j];
		   		if (el.type == 'checkbox' || el.type == 'radio') {
					if (_el.checked) value[value.length] = _el.value;
		   		} else if (_el.type == 'file') {
		   			value.concat(new Array(_el.files));
	   			} else{
	   		        value[value.length] = _el.value;
 			    }
 			}
   			ignore_fields.push(el.name);
        }
        data[el.name] = value;
    }

    if (use_filter) return {'data': data, 'is_error':false};
    else return data;
}

function set_form_fields(data, form) {
	function normalize_boolean(value) {
	    if (value == 'true') return true;
	    else if (value == 'false') return  false;
	    else return value;		
	}
	
	var name, value, el;
	for (name in data) {
		//if (data.hasOwnProperty(name)) continue;
        if (form[name] === undefined) continue;
		value = data[name];
		el = form[name];
		if (el.tagName !== undefined) {
			if (el.type == 'checkbox' || el.type == 'radio') 
			    el.checked = normalize_boolean(data[name]);
			else { el.value = data[name]; }
		} else {
			for (var j = 0; j<el.length; j++) {
				if (el[0].checked !== undefined) {
					if (data[name].indexOf(el[j].value) != -1) el[j].checked = true;
				} else { el[j].value = data[name][j]; }
			}
		}
    }
}

function YandexUniMap() {
    var map_obj = null;
    var self = this;

    this.onready = function(func, map_id) {
        self.map_id = map_id;
        ymaps.ready(function() {func(self);});
    }

    this.address2coords = function(address, onAddress2Coord_success, onAddress2Coord_error) {
        var myGeocoder = ymaps.geocode(address);
        myGeocoder.then(
            function (res) {
                onAddress2Coord_success(res.geoObjects.get(0).geometry.getCoordinates());
            },
            function (err) {
                onAddress2Coord_error('Ошибка');
            }
        );
    }

    this.coords2address = function(address, onAddress2Coord_success, onAddress2Coord_error) {
        var myGeocoder = ymaps.geocode(address);
        myGeocoder.then(
            function (res) {
                onAddress2Coord_success(res.geoObjects.get(0).geometry.getCoordinates());
            },
            function (err) {
                onAddress2Coord_error('Ошибка');
            }
        );
    }
 
    this.buildMap = function(center, zoom) {
        //alert(self)
        self.map_obj = new ymaps.Map(self.map_id, {
            'center': center,
            'zoom': zoom
        });
    }
    
    this.addPoint = function(coords) {
        var myGeoObject = new ymaps.GeoObject({
            geometry: {
                type: "Point",// тип геометрии - точка
                coordinates: coords // координаты точки
           }
        }, {draggable:true});
        self.map_obj.geoObjects.add(myGeoObject);
    }

    this.setCenter = function(coords, ms) {
        if (ms == undefined || ms == 0) self.map_obj.setCenter(coords);
        else self.map_obj.panTo(coords, {delay: ms});
    }

    this.init = function() {
        self.map_obj = new ymaps.Map("map", {'center': [58, 37], 'zoom':7});
    } 

}

function onclick_field(field, default_value) {
	if (field.value == default_value) {field.value = '';}
	field.addEventListener('blur', blur_field);
	field.dataset.default = default_value;
}
function blur_field(field) {
	var field = field.target;
	var default_value = field.dataset.default;
	if (field.value == '') {field.value = default_value;}
	field.removeEventListener('blur', blur_field);
}

function Tabs(headers_id, content_id, styles) {
	var self = this;
	this.styles = styles;

    this.setStyle2Element = function(el, styles) {
    	for (var prop in styles) el.style[prop] = styles[prop];
    }

    this.getTabName = function(tab_content) {
        return tab_content.id.split('_').splice(1).join('_')
    }

	function init(header_id, content_id) {
		if (!document.getElementById(content_id)) {console.log('Инициалзация вкладок: отсутствует вкладка '+content_id); return}
		var tab_contents = document.getElementById(content_id).children;
		for (var i = 0; i < tab_contents.length; i++) {
			var tab_content = tab_contents[i];
			//console.log(tab_content.id.split('_').splice(1));
			var name = self.getTabName(tab_content);
			var div = document.createElement('div'); div.addEventListener('click', function() {self.show_tab(self.getTabName(this));});
            div.innerHTML = tab_content.dataset.title;
            div.id = "tabhead_"+name;
            document.getElementById(header_id).appendChild(div);
			//document.getElementById(header_id).innerHTML += '<div id="tabhead_'+name+'" onclick="show_tab2(\''+name+'\')">'+tab_content.dataset.title+'</div>'
		}
	}

	this.show_tab = function(new_id) {
		if (!document.getElementById("tabhead_"+new_id)) {console.log('Показ вкладки: отсутствует вкладка '+new_id); return}
		var headers = document.getElementById("tabhead_"+new_id).parentElement;
		// скрываем текущую вкладку
		if (headers.dataset.hasOwnProperty('current')) {
			document.getElementById("tabcon_"+headers.dataset.current).style.display = "none";
		    self.setStyle2Element(document.getElementById("tabhead_"+headers.dataset.current),  self.styles['tab_header_deselected']) //document.getElementById("tabhead_"+headers.dataset.current).style.backgroundColor = "#999999";
		}
		// показываем новую вкладку
		document.getElementById("tabcon_"+new_id).style.display = "block";
		self.setStyle2Element(document.getElementById("tabhead_"+new_id), self.styles['tab_header_selected'])// document.getElementById("tabhead_"+new_id).style.backgroundColor = "#8888bb";
		// сохраняем новый id как текущий
		headers.dataset.current = new_id;
	}

	//if (typeof headers_id == 'string') headers_id = [headers_id];
	//for (var i=0; i < headers_id.length; i++) init(headers_id[i]);
	init(headers_id, content_id)
}

function len_base64(str, kilo) {
	kilo = kilo || 'B';
	kilos = {'B': 1, 'KB':1024, 'MB':1024*1024};
	return str.length * 6 / 8 / kilos[kilo];
}

function del_time_mark(url) {
	return url.replace(/\?time_mark=[0-9a-z]+$/, '');
}
function set_time_mark(url) {
	return url+'?time_mark='+(new Date()).getTime(); // Math.random()
}
function update_time_mark(url) {
	return set_time_mark(del_time_mark(url));
}
function is_data_url(s, format, code) {
    format = format || 'image/png';
    if (typeof format == 'object') {format = format.join('|'); format = '(?:'+format+')';}
    code = code || 'base64';
	return s.match((new RegExp('^data:'+format+';'+code+','))) === null ? false : true;
}

// http://stackoverflow.com/questions/16245767/creating-a-blob-from-a-base64-string-in-javascript
function base64toFile(data_url, contentType, fileName) {
    var byteCharacters = atob(data_url.replace(/^data.*,/, ''));
    var byteNumbers = new Array(byteCharacters.length);
	for (var i = 0; i < byteCharacters.length; i++) {
	    byteNumbers[i] = byteCharacters.charCodeAt(i);
	}
    var data = new Uint8Array(byteNumbers);
    data = new Blob([data], {type: contentType});
    var blob = new File([data], fileName, {type: contentType});
    return blob;
}

// функци options['func_filter'] в случае верности возвращает true, иначе - текст ошибки.
function sendform(button, action, options) {
	if (options === undefined) options = {};

	options['func_success'] = options['func_success'] || options['func_after_success'];

    // получаем форму, если указана
    if (options['form'] === undefined) {
    	if (button.form != null && button.form != undefined) options['form'] = button.form;
    	else if (button.closest('form') != null && button.closest('form') != undefined) options['form'] = button.closest('form');
    }
    // значения по умолчанию
    if (options['func_transform_fields'] === undefined) { options['func_transform_fields'] = function(fields, form) {return fields;}; }
    if (options['func_filter'] === undefined) { options['func_filter'] = function(fields) {return true;}; }
    if (options['answer_type'] === undefined) { options['answer_type'] = 'ButtonProgress'; }

    // получаем данные с формы, модифицируем и фильтруем
	if (options['form'] !== undefined) { var fields = get_form_fields(options['form']); }
	else {var fields = {};}
	fields = options['func_transform_fields'](fields, options['form']);
	var is_true = options['func_filter'](fields); // is_true в случае ошибки должен возвратить массив ошибок

    if (options['data'] !== undefined) {
        for (var prop in options['data']) {
            if (options['data'].hasOwnProperty(prop)) fields[prop] = options['data'][prop];
        }
    }

    // отсылаем данные на сервер
	if (typeof is_true == 'string') is_true = [is_true];
	if (options['answer_type'] == 'ButtonProgress') {
    	if (is_true === true) RA_ButtonProgress(action, fields, button, 'Отправляем...', options['func_success'], options);
	    else show_button_message(button, is_true.join('<br>'));
	} else if (options['answer_type'] == 'Notification') {
    	if (is_true === true) RA_Notification(action, fields, options['func_success'], options);
	    else showNotification(is_true.join('<br>'), 'error');
	}
}

// функции для админ-панели
function setTabFromBackup() {
	var tab_name = localStorage.getItem('tab_name') || 'page';
	var content_name = localStorage.getItem('content_name') || 'index';
	var args = JSON.parse(localStorage.getItem('args') || '{}');
	show_tab(tab_name, args, content_name);
}
function saveTabToBackup(tab_name, args, content_name) {
	localStorage.setItem('tab_name', tab_name);
	localStorage.setItem('content_name', content_name);
	localStorage.setItem('args', JSON.stringify(args || {}));
}

function get_from_ckEditor() {
	return document.getElementsByClassName('cke_wysiwyg_frame')[0].contentDocument.body.innerHTML;
}

function ZIndex(start_index) {
	var self = this;
	start_index = start_index || 1;
	
	this.els = [];
	
	this.lift = function(el, level) {
		if (level == 'top') {
			self.remove(el);
			self._add(el, 'top');
		}
	};

	this._add = function(el, level) {
		if (level == 'top') {
			self.els[self.els.length] = el;
			el.style.zIndex = self.els.length-1+start_index;
		}
	};
    this.ev_to_top = function(e) {zi.lift(e.currentTarget, 'top');}
	this.add = function(el, level) {
		self._add(el, level);
        el.addEventListener('mousedown', self.ev_to_top);
        el.addEventListener('touchstart', self.ev_to_top);
	};
	this.remove = function(el, level) {
	    self.els.splice(parseInt(el.style.zIndex), 1);
	    self.indexate();
	};

    this.indexate = function() {
    	for (var i=0; i<self.els.length; i++) self.els[i].style.zIndex = i+start_index;
    };
}

// start_index = 2, так как навигационная панель имеет значение z-index = 1
var zi = new ZIndex(2);

// показываем несколько капч. Вероятно, это костыль, поэтому необходимо почитать, что гугл пишет по этому поводу.
function render_grecaptcha() {
	var widget_id;
	var holders = document.getElementsByClassName('g-recaptcha');
	for(var i=0; i < holders.length; i++) {
		if (holders[i].children.length > 0) {
            if (document.getElementById(holders[i].id + '_input').value !== '') continue; // если id есть, то значит, мы его уже рендекрили
			widget_id = 0; // должен встречаться только один раз, так как гугл автоматически рендерит только первую капчу.
		} else widget_id = grecaptcha.render(holders[i], {}, holders[i].dataset.sitekey);
		document.getElementById(holders[i].id + '_input').value = widget_id;
	}
}

/**
 * Контекстное меню
 */
function ContextMenu(id, items) {
	var self = this;
	this.cm = undefined;
	this.target_item = undefined; // элемент контекстного меню, на который щёлкнули
	this.target_el = undefined; // элемент, на котороом щёлкнули для показа контекстного меню
	this.events = [];
	this.id = id;

    function init() {
    	var cm = document.getElementById(id);
        if (!cm) {
	        cm = document.createElement('ul');
	        cm.className = 'context_menu'; cm.id = 'context_menu';
	        
	        var li;
	        for(var i=0; i<items.length; i++) {
	            var li = document.createElement('li');
	            li.textContent = items[i][0];
                li.dataset.index = i;
	            li.addEventListener('click', self.select_item);
	            self.events[i] = items[i][1];
	            cm.appendChild(li);
	        }
        }
    	cm.style.position = 'fixed'
        self.cm = cm;
    	document.body.appendChild(cm);
    	zi.add(cm, 'top');
    }

	this.select_item = function(e) {
		self.target_item = e.currentTarget;
		//zi.remove(self.cm);
		self.cm.style.display = 'none';
		console.log(self.target_el);
		self.events[self.target_item.dataset.index](e, self);
	}

    this.open = function (e) {
    	//var cm = document.getElementById('context_menu');
    	self.cm.style.display = 'block'
    	self.cm.style.top = e.clientY + 'px';
    	self.cm.style.left = e.clientX + 'px';
    	zi.lift(self.cm, 'top');
    	self.target_el = e.currentTarget;
    	return false;
    }
    
    init();
}

/**
 * Модуль (внутри ячеек)
 */
function PageModule() {
	var self = this;
}

/**
 * Модель страницы, 
 * 
 * Модель и страница строятся согласно шаблону.
 * Модель состоит из строк с иконками модулей. Страница состоит из строк с модулями.
 */
function PageStructure(rows) {
	var self = this;
	this.rows = rows;

    this.count_row = function(row) {
		return (row.children.length - 1) / 2;
    }

    this.count_cells = function(row) {
		return (row.children.length - 1) / 2;
    }

    this.remove_row = function(row) {
		row.nextElementSibling.remove();
		row.remove();
    }

    this.create_vacant = function(level) {
   		var vacant = document.createElement('div');
   		vacant.className = 'vacant_'+level;
        vacant.addEventListener('click', self.end_selecting_vacant)
   		return vacant;
    }

    this.calc_width_row = function(row, event_func=false) {
		if (event_func && row.className == 'vacant_row') row.addEventListener('click', event_func);
		for(var j=0; j<row.children.length; j++) {
			var cell = row.children[j];
			if (cell.className == 'vacant_cell') {
				if (event_func) cell.addEventListener('click', event_func);
				continue;
			}
            cell.style.width = (100/self.count_cells(row) - 5) + '%'; // 3px - ширина вакантного места
		}
    }

    this.calc_width = function(event_func=false) {
		for(var i=0; i<self.rows.children.length; i++) {
			self.calc_width_row(self.rows.children[i], event_func);
		}
    }

    /**
     * @todo переписать с использованием self.even_cell
     */
    this.get_row_number = function(vacant) {
    	var path = {};
    	
    	if (vacant.className == 'vacant_row') {
    		path.cell_num = 0;
    		path.is_new_row = true;
    		
    	    var rows = self.rows;
    	    for (var i=0; i<rows.children.length; i++) {
    	    	if (rows.children[i].className != 'vacant_row') continue;
    	    	console.log(rows.children[i]);
    	    	if (vacant == rows.children[i]) {path.row_num = (i-2)/2; break;}
    	    }
    	} else if (vacant.className == 'vacant_cell') {
    		path.is_new_row = false;

    		var row = vacant.parentElement;
    	    for (var i=0; i<row.children.length; i++) {
    	    	if (row.children[i].className != 'vacant_cell') continue;
    	    	if (vacant == row.children[i]) {path.cell_num = (i-2)/2; break;}
    	    }
    	    
    	    var rows = row.parentElement;
    	    for (var i=0; i<rows.children.length; i++) {
    	    	if (rows.children[i].className != 'vacant_row') continue;
    	    	if (row.nextElementSibling == rows.children[i]) {path.row_num = (i-2)/2; break;}
    	    }
    	}

   	    return path;
    	
    }

    this.replace_icon = function(vacant, icon) {
    	old_icon_row = icon.parentElement;
    	icon.nextElementSibling.remove();

    	if (vacant.className == 'vacant_row') {
    		var icon_row = document.createElement('div');
    		vacant.parentElement.insertBefore(icon_row, vacant.nextElementSibling);
    		vacant.parentElement.insertBefore(self.create_vacant('row'), icon_row.nextElementSibling);

    		icon_row.appendChild(self.create_vacant('cell'));
    		icon_row.appendChild(icon);
    		icon_row.appendChild(self.create_vacant('cell'));
    	} else if (vacant.className == 'vacant_cell') {
    		vacant.parentElement.insertBefore(icon, vacant.nextElementSibling);
    		vacant.parentElement.insertBefore(self.create_vacant('cell'), icon.nextElementSibling);
    	}

    	if (self.count_cells(old_icon_row) == 0) self.remove_row(old_icon_row);
    	else self.calc_width_row(old_icon_row);
    	
    }
    
    /**
     */
    this.even_cell = function(options) {
    	options = options || [];
		for(var i=0; i<self.rows.children.length; i++) {
			var row = self.rows.children[i];

			if (options['func_row']) options['func_row'](row, i);
			if (!options['func_cell']) continue;

			for(var j=0; j<row.children.length; j++) {
				var cell = row.children[j];
				options['func_cell'](row, i, cell, j);
			}
		}
    }

    this.template_str2arr = function(strTemplate, module_data) {
    	strTemplate = strTemplate.split('-');
    	for (var i=0; i<strTemplate.length; i++) {
    		strTemplate[i] = strTemplate[i].split(',');
    	}
    	return strTemplate;
    }

    this.template_arr2str = function(arrTemplate) {
    	for (var i=0; i<arrTemplate.length; i++) {
    		var row = [];
    		for (var j=0; j<arrTemplate[i].length; j++) {row[row.length] = arrTemplate[i][j].id;}
    		arrTemplate[i] = row.join(',');
    	}
    	console.log(arrTemplate);
    	return arrTemplate.join('-');
    }
    
    /**
     * Строим иконки страницы согласно шаблону
     */
    this.build_by_template = function(arrTemplate, isInteractive) {
    	//isInteractive = isInteractive === undefined ? true : isInteractive;

        var html_icons = "";
        /*if (isInteractive)*/ html_icons = "<div class='vacant_row'></div>";
        for (var i=0; i<arrTemplate.length; i++) {
        	var row = arrTemplate[i];
            html_icons += "<div>";
            /*if (isInteractive)*/ html_icons += "<div class='vacant_cell'></div>";
            for (var j=0; j<row.length; j++) {
                html_icons += "<div data-id='"+row[j].id+"' title='"+row[j].name+"'>"+row[j].name+"</div>";
                /*if (isInteractive)*/ html_icons += "<div class='vacant_cell'></div>";            	
            }
            html_icons += "</div>";
            /*if (isInteractive)*/ html_icons += "<div class='vacant_row'></div>";    	
        }
        self.rows.innerHTML = html_icons;
    }
}

/**
 * Страница
 */
function Page(rows) {
	var self = this;
	
	this.rows = rows;

    this.calc_width_row = function(row) {
		for(var j=0; j<row.children.length; j++) {
			var cell = row.children[j];
            cell.style.width = (100/row.children.length) + '%';
		}
    }

    this.calc_width = function(rows) {
		for(var i=0; i<self.rows.children.length; i++) {
			self.calc_width_row(self.rows.children[i]);
		}        	
    }

    this.replace_module = function(path, icon) {
    	var cell = document.getElementById("cell_pmodule"+icon.dataset.id);
        var old_row = cell.parentElement;

    	if (path.is_new_row) { // вставляем в новую строку
            var to_row = document.createElement('div'); to_row.className = 'pm_row';
            self.rows.insertBefore(to_row, self.rows.children[path.row_num+1]);
            var before_cell = null;
    		
    	} else { // вставляем в имеющуюся строку
    	    var to_row = self.rows.children[path.row_num];
    	    var before_cell = to_row.children[path.cell_num+1];

    	}

        to_row.insertBefore(cell, before_cell);
   	    if(old_row.children.length == 0) old_row.remove();
    	self.calc_width();
    }

    /**
      * Делаем массив-шаблон на основании страницы
      */
    this.make_template = function() {
    	var template = [];
    	for (var i=0; i<self.rows.children.length; i++) {
    		var trow = [];
    		var row = self.rows.children[i];
    		
    		for (var j=0; j<row.children.length; j++) {
    			trow.push({
    				name: row.children[j].dataset.name,
    				id: row.children[j].dataset.id
    			});
    		}
    		
    		template.push(trow);
    	}
    	
    	//console.log(template);
    	return template;
    }
}

/**
 * Параллельное управление страницей и её моделью
 */
function PageEditor(page_rows, struct_rows, options, context_menu_items) {
	var self = this;
	this.cm = new ContextMenu('context_menu', context_menu_items);
	this.cm.pe = self;
	this.page = new Page(page_rows);
	this.page_struct = new PageStructure(struct_rows, options);

	options = options || [];
	this.btn_save = options['btn_save']

    this.end_selecting_vacant = function(e) {
    	var vacant = e.currentTarget;
    	var icon = self.cm.target_el;
    	//console.log(vacant, icon);

    	var path = self.page_struct.get_row_number(vacant);
    	console.log(path)

    	self.page_struct.replace_icon(vacant, icon);
    	self.page_struct.calc_width_row(icon.parentNode);
    	self.page.replace_module(path, icon);

	   	self.page_struct.even_cell({
	   		func_cell: function(row, i, cell, j) {
				if (cell.className == 'vacant_cell') return;
				cell.addEventListener('click', self.cm.open);
	   		}
	   	});
    };

    this.start_selecting_vacant = function() {
    	var style = document.createElement('style');
    	style.textContent = `
    	    #module_icons .vacant_row:hover, #module_icons .vacant_cell:hover {
    	    	background: red; cursor:pointer;
    	    }
    	`;
    	document.body.appendChild(style);
	   	self.page_struct.even_cell({
	   		func_cell: function(row, i, cell, j) {
				if (cell.className == 'vacant_cell') return;
				cell.removeEventListener('click', self.cm.open);
	   		}
	   	});
    }

    this.ev_open_context_menu = function(e) {
    	self.cm.open(e);
    }

    this.btn_save_click = function(e) {
    	var btn = e.target;
    	
    	var el_template = document.createElement('input');
    	el_template.type = 'hidden'; el_template.name = 'template';
    	btn.form.appendChild(el_template);

        var arrTemplate = self.page.make_template();
        var strTemplate = self.page_struct.template_arr2str(arrTemplate);
    	
    	btn.form.template.value = strTemplate;
    	
    	sendform(btn, 'cabinet_edit_page_template_custom', {func_after_success: function() {}});
    }

	this.init = function() {
		var template = self.page.make_template();
		self.page_struct.build_by_template(template);

        self.page_struct.calc_width(self.end_selecting_vacant);
	   	self.page_struct.even_cell({
	   		func_cell: function(row, i, cell, j) {
				if (cell.className == 'vacant_cell') return;
				cell.addEventListener('click', self.cm.open);
	   		}
	   	});
	   	
	   	if (options['btn_save']) options['btn_save'].addEventListener('click', this.btn_save_click);
	}
	
	this.init();

}

function open_edit_page_window() {
	var w = W.open('edit_page_window', {text_title:'Редактирование страницы'});
	var context_menu_items = [['Редактировать'], ['Оформление'], ['Переместить', function(e, cm) {
	   		cm.pe.start_selecting_vacant();
	   		//console.log('Щёлкнули на: ', cm.target_el);
	   	}]];
	pe = new PageEditor(
		document.querySelector('.pm_rows'),
		w.querySelector('#module_icons'),
        {btn_save: w.querySelector('#module_icons-btn_save')},
		context_menu_items
	);
}

/**
 * Запускает скрипты в коде html, вставленнном в страницу.
 */
function run_inserted_scripts(tag) {
   	var ss = tag.getElementsByTagName("SCRIPT")
   	for (var i = 0; i < ss.length; i++) {
   		var s = ss[i]
        var g = document.createElement("SCRIPT");
   		if (s.src!='') { g.src = s.src; } // также подключает внешние скрипты
   		else {
   			blob = unescape( encodeURIComponent(s.text));
   			g.src = "data:text/javascript;charset=utf-8;base64,"+btoa(blob)
   		}
 		g.async = false;
   		s.parentElement.insertBefore(g, s)
   		s.remove()
   	}
}

/* Подгружаемые Вкладки */
function get_tab_content(tab_name, content_name, args, options) {
	options = options || [];
	options['tag_content'] = options['tag_content'] || document.getElementById("tab_content");
	options['backup'] = options['backup'] === undefined ? true : options['backup'];
	
	if (typeof clear_pager !== 'undefined') clear_pager();

	args = args || {}
	options['tag_content'].innerHTML = 'грузим... :)';
    RequestAction('get_tab', undefined, {'tab_name': tab_name, 'content_name': content_name, 'args':JSON.stringify(args)}, function() {
       	// загружаем содержимое вкладки
       	if (this.readyState != 4) return;

        if (this.status==200) {
        	// из-за касперского поменял формат данных (json -> plain text)
    	    //var res = JSON.parse(this.responseText);
    	    //if (res['success'] == 1) var content = res['data'];
    	    //else {var content = 'ошибка сервера: '+res['message'];}
    	    var content = del_casper(this.responseText);
    	    if (content[0] == '{') {
    	    	// но json-формат оставил как опцию :)
        	    content = JSON.parse(content);
        	    if (content['success'] == 1) var content = content['data'];
        	    else {content = 'ошибка сервера: '+content['message'];}
    	    }
        }
        else {var content = 'неизвестная ошибка(';}

        // вставляем содержимое вкладки на страницу
        //location.hash = tab_name+'-'+content_name+'-'+JSON.stringify(args);
		if (options['backup']) saveTabToBackup(tab_name, args, content_name);
   		options['tag_content'].innerHTML = content;

        // запускаем скрипты
        run_inserted_scripts(options['tag_content']);
    });
}

function show_tab(tab_name, args, content_name) {
	if ((typeof tab_name) == 'object') {tab_name = tab_name.id.slice(8);}
	content_name = content_name || 'index'
	
	var headers = document.getElementById("tabhead_"+tab_name)
	if (headers === null) {console.log('головы с именем "'+tab_name+'" не найдены!'); return}
	headers = headers.parentElement;
	// скрываем текущую вкладку (если была открыта)
	if (headers.dataset.hasOwnProperty('current')) {
		document.getElementById("tabhead_"+headers.dataset.current).style.background = "";
		document.getElementById("tabhead_"+headers.dataset.current).style.color = "";
		//document.getElementById("tabcon_"+headers.dataset.current).style.display = "none";
	}
	// показываем новую вкладку
	//document.getElementById("tabcon_"+new_id).style.display = "block";
	document.getElementById("tabhead_"+tab_name).style.background = "rgb(250, 241, 67)";
	document.getElementById("tabhead_"+tab_name).style.color = "#e31e24";

	// сохраняем новый id как текущий
	headers.dataset.current = tab_name;

	get_tab_content(tab_name, content_name, args);
}


/**
 * Устанавливает новый id города на портале. Глобально.
 * @param Settlement self - объект города, так как на портале может присутствовать одновременно несколько форм выбора города: для мобильной и настольной версий.
 */
function set_global_settlement(settlement_id, self) {
	setSettlementTitle(self);
    // очищаем текущее значение
    var date = new Date();
    date.setDate(date.getDate() - 1);
    document.cookie = 'settlement=; expires='+date.toUTCString()+'; path=/'

    // устанавливаем текщее значение
    if (settlement_id !== null) {
	    date.setDate(date.getDate() + 365);
	    document.cookie = 'settlement='+settlement_id+'; expires='+date.toUTCString()+'; path=/'
	    self.settlement_id = settlement_id;
    }

    window.location = window.location;
}

/* начало паджинатора */

function clear_pager(offset, count, nav) {
	if (nav === undefined) nav = document.getElementById('bottom');
	if (!nav) return;
    //while(nav.children.length != 2) nav.children[2].remove();
    var for_del = [];
    for(var i = 0; i < nav.children.length; i += 1) {
    	if (nav.children[i].className == 'num_page') for_del.push(nav.children[i]);
    }

    for(var i = 0; i < for_del.length; i += 1) for_del[i].remove();
	
};

function show_pager(cur, max, func_url, nav) {
	
	var ON_SIDE = 3;
	if (nav === undefined) nav = document.getElementById('bottom');
    var divs = [
    	['1', 'url'],
    	[cur, 'text'],
    	[max, 'url']
    ];

	for (var i=cur-1; i>=cur-ON_SIDE; i--) {
		if (i<=1) continue;
		divs.splice(1, 0, [i, 'url']);
	}

	if (cur==1) divs.splice(0, 1);
	if (cur-ON_SIDE > 2) divs.splice(1, 0, ['...', 'text']);

	for (var i=cur+1; i<=cur+ON_SIDE; i++) {
		if (i>=max) continue;
		divs.splice(-1, 0, [i, 'url']);
	}
	
	if (cur+ON_SIDE < max-1) divs.splice(-1, 0, ['...', 'text']);
	if (cur==max) divs.splice(-1, 1);

    var url, div, textContent;
    for (var i=0; i<divs.length; i++) {
		//var div = document.createElement('div'); div.style.marginRight = '5px';

    	//if (divs[i][1] == 'url') div.innerHTML = "<a href='"+func_url(divs[i][0])+"'>"+divs[i][0]+"</a>";
    	//else if (divs[i][1] == 'text') {div.textContent = divs[i][0]; div.style.background = '#ffffff'; div.style.color = '#ca0c11';}

		var div = document.createElement('a'); div.style.marginRight = '5px';
        div.textContent = divs[i][0]; div.className = 'num_page';

    	if (divs[i][1] == 'url') { div.href = func_url(divs[i][0]); }
    	else if (divs[i][1] == 'text') {div.style.background = '#ffffff'; div.style.color = '#ca0c11';}

    	nav.appendChild(div);
    }

};
/* конец паджинатора */

function show_image(input, img) {
	//var form = input.closest('form');
	var reader = new FileReader();
   	reader.onload = function(event) {
        var image_data = event.target.result;
        //console.log(prefix+id_pic);
        img.src = image_data;
        img.style.display = 'inline-block';
        img.dataset.last = 'set';
    };
    reader.readAsDataURL(input.files[0])
   	//reader.readAsDataURL(form[id_pic].files[0])
}

function content_by_api(api, tag, options) {
    options['is_escape_content'] = options['is_escape_content'] === undefined ? false : options['is_escape_content'];
    options['func_after_insert'] = options['func_after_insert'] === undefined ? function() {} : options['func_after_insert']; 
    
    RA_raw(api, options['data'], {
	    func_after_load: function(res) {
    		if (options['is_escape_content']) tag.text_content = res['message'];
    		else tag.innerHTML = res['message'];
    		options['func_after_insert']();
    		run_inserted_scripts(tag);
	    },
    	func_fatal: function(err_text) {
            tag.text_content = err_text;
    	},
    	url: options['url']
    });
}
