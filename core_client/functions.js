/*
Author: Polyakov Konstantin
Licence: Domain Public

You can use this code for everything! But be very carefull :)
*/

function Request(method, url, post, async_func) {
	if (url === undefined) url = WB_URL+'/modules/wbs_core/api.php'; 

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
	        if (options['func_error']) options['func_error'](res, options['arg_func_error']);
	    }
	    if (res['location']) wiindow.location = res['location'];
        } else if (!navigator.onLine) {
            if (options['func_fatal']) options['func_fatal']('Нет соединения с Интернет');
        } else {
            if (options['func_fatal']) options['func_fatal']('Неизветсная ошибка');
        }
        if (window.grecaptcha && data['grecaptcha_widget_id']) grecaptcha.reset(data['grecaptcha_widget_id']); // сбрасываем капчу гугла
        if (options['wb_captcha_img']) wb_captcha_reload(options['wb_captcha_img']); // сбрасываем капчу websitebaker
    });
}

function show_button_message(button, message, timeout) {
    var process;
    if (button.nextSibling === null || button.nextSibling.className != 'RA_ButtonProgress') {
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
                if (field.type == 'button') continue;
                if (absent_fields.indexOf(field.name) != -1) {field.style.border = '1px solid red'; field.style.background = '#ffe6e6';}
		else {
                    field.style.border = null; field.style.background = null;

                    //field.style.border = '1px solid green'; field.style.background = '#e6ffe6';
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
            light_absent_fields(button.form, []) // уберём красноту с полей, если они до этого были неверными.
            if (func_success) func_success(res, options['arg_func_success']);
    	},
    	func_error: function(res) {
            show_button_message(button, 'ошибка: '+res['message']);
            animate_element(button, 'btn-err')
            if (res['absent_fields'] !== undefined) light_absent_fields(button.form, res['absent_fields']);
            if (options['func_error']) options['func_error'](res, options['arg_func_error']);
    	},
    	func_fatal: function(res) {
            show_button_message(button, 'неизвестная ошибка(');
    	},
    	url: options['url'],
    	func_after: options['func_after'],
        wb_captcha_img: options['wb_captcha_img']
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
    	func_after: options['func_after'],
        wb_captcha_img: options['wb_captcha_img']
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

function get_from_ckEditor(el=null) {
    if (el === null) el = document;
    return el.getElementsByClassName('cke_wysiwyg_frame')[0].contentDocument.body.innerHTML;
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

    var reader = new FileReader();

    reader.onload = function(event) {

        var image_data = event.target.result;

        if (img.src) {

            img.dataset.url = img.src;

            img.src = image_data;

        } else {

            img.dataset.url = img.style.backgroundImage;

            img.style.backgroundImage = "url(" +image_data+ ")";

        }

    };

    reader.readAsDataURL(input.files[0])

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

function set_params(params, options) {
    if (options === undefined) options = [];
    if (options.reload === undefined) options.reload = true;
    if (options.search === undefined) options.search = location.search;
    if (options.url === undefined) options.url = location.protocol +'//'+ location.host + location.pathname;
        
    let search = new URLSearchParams(options.search);
    for (let param in params) {
        if (!params.hasOwnProperty(param)) continue;
        if (params[param] === null) search.delete(param);

        else search.set(param, params[param]);
    }
    
    if (options.reload) {
        //window.location.search = search.toString();
        window.location.href = options.url +'?'+ search.toString();
    } else {
        return search.toString();
    }
}

function wb_captcha_reload(img){

    let a = document.createElement('a');

    a.href = img.src;

    img.src = set_time_mark(a.protocol + '//' + a.hostname + a.pathname);

}


function DND(element, options) {
    function dnd(e) { // drag and drop
        e.currentTarget.ondragstart = function() {return false;};
        document.body.onmousedown = function() {return false;}; // выключаем  выделение текста
        options['data'] = options['data'] || {};
        options['data']['isSensorDisplay'] = e.touches === undefined ? false : true
        
        if (options['down']) options['down'](e, options['data']);
        
        function end(e) {
            document.removeEventListener('mousemove', move);
            document.removeEventListener('mouseup', end);
            document.removeEventListener('touchmove', move);
            document.removeEventListener('toucend', end);
            document.body.onmousedown = function() {return true;}; // включаем  выделение текста
            if (options['up']) options['up'](e, options['data']);
        }
        
        function move(e) {
            if (options['move']) options['move'](e, options['data']);
        }
        document.addEventListener('mousemove', move);
        document.addEventListener('mouseup',  end);
        document.addEventListener('touchmove', move);
        document.addEventListener('touchend', end);
    }
    
    var _dnd = dnd;
    element.addEventListener('mousedown', _dnd); // для мыши
    element.addEventListener('touchstart', _dnd); // для сенсорного дисплея
}