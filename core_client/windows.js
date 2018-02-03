/*
Author: Konstantin Polyakov
Licence: Public Domain
Date: 2015 - 2017 years

25.06.2016 не улетает, вниз может уходить до заголовка окна (это  позволяет быть окну сколь угодно длинному)
--.--.---- - окно нормально работает при листании страниц (не прыгает, не убегает от курсора)
01.09.2016 - поддержка сенсорных устройств
19.09.2016 - установка настроек окна при его открытии
05.12.2016 - окна переписаны в виде класса. Написана функция для открытия окна с соержимым, получаемого с сервера через API. Контейнер тела можно генерировать динамически. Написана функция для замены содержимого тела. Окно открывается по центру.
06.12.2016 - исправлена ошибка с указанием максимального количества отображаемых окон. Функция drag-n-drop отделена от функционала окна.
07.12.2016 - удалён старый код. Использовать только новый класс окна.
12.01.2017 - заголовок можно указывать на теле окна или передать в options.
29.01.2017 - проверка на наличие класса ZIndex, добавлена опция 'add_sheet'
10.02.2017 - Добавлена опция autoclose, эффект "медленного" закрытия
15.02.2017 - Добавлена проверка на наличие класса у тела окна в функции createWindow
13.04.2017 - Перенесён расшиернный класс окна из Инфо.РФ. При открытии окна можно задать опцию url.
*/

function _getCoords(elem) { // кроме IE8-  // утащено отсюда --> https://learn.javascript.ru/coordinates-document#getCoords. Очень благодарен указаному сайту, так почти всегда обращаюсь к нему. Иногда - сайт Мозиллы.
    var box = elem.getBoundingClientRect();
    return {
        top: box.top + pageYOffset*0,    // помножили на ноль значение того, на сколько прокрученав странца. Если убрать ноль, то вместо позиции на окне будет указана позиция на странице.
        left: box.left + pageXOffset*0
    };
}

function _Window() {
        var self = this;

    this.arrData = {}; // хранятся связанные с окном объекты

    this._open = function(w) {
        
    }
        
    this.get_wtitle = function(w) { return w.querySelector('.windowTitle'); };
    this.get_wbody = function(w) { return w.querySelector('.windowBody'); };
        this.get_w = function(child_el) {
        //return child_el.closest(".windowWindow");

                //if (child_el.closest) return child_el.closest(c);
                // for old browsers;
            //if (child_el.className == "windowWindow") {return child_el;}
            //else {return self.get_w(child_el.parentElement);}

            return $(child_el).closest(".windowWindow")[0];
            //return dt.closest(child_el, ".windowWindow");
        };

        this.get_data = function(w) { return self.arrData[w.id]; }
        this.set_data = function(w, data) { self.arrData[w.id] = data; }
        this.del_data = function(w) {  delete self.arrData[w.id]; }
}

// использует класс ZIndex
function Window() {
        _Window.call(this);
        var self = this;
    this.count_open = {}; // количество открытых одинаковых окон
    this.count_open_total = 0 // всего открытых окон
    this.close_direction = 'left';

    var _w = document.createElement('div'); _w.className = 'windowWindow'; _w.id = 'windowWindow';
    _w.innerHTML = 
            "<div class=\"windowTitle\">"+
                "<div class=\"windowBClose\" title=\"Закрыть окно\">X</div>"+
                "<div class=\"windowTitleText\"></div>"+
                "</div>";
    _w.style.position = "fixed";
    this._w = _w;

    this.zi = (typeof ZIndex != 'undefined' && typeof zi != 'undefined' && zi instanceof ZIndex) ? zi : null;

    this.default_options = {
                create_method: 'clone',
                position: 'center',
                max_count: false,
                add_sheet: false,
                autoclose: false,
                add_title: true,
                class_title: null,
                class_body: null,
                class_window: null,
                text_title: 'Без названия',
                body_content: undefined
    };

    this.createBodyByClone = function(idBody) {
            var wbody = document.getElementById(idBody).cloneNode(true);
            return wbody;
    }

    this.createBodyByCreate = function(idBody) {
            var wbody = document.createElement('div');
            wbody.id = idBody;
            wbody.className = 'windowBody';
            return wbody;
    }

    this.createWindow = function(idBody, method, replaceContentBody) {
        method = method || 'clone';
        replaceContentBody = replaceContentBody || undefined;

        var wbody, w = self._w.cloneNode(true);
        // Создание тела
        if (method == 'clone') { // берём готовое тело
            wbody = self.createBodyByClone(idBody);
        } else if (method == 'create') { // создаём пустое тело
            wbody = self.createBodyByCreate(idBody);
        } else if (method == 'auto') { // если готового тела нет, то создваём пустое, иначе берём готовое.
            if (document.getElementById(idBody)) wbody = self.createBodyByClone(idBody);
            else { wbody = self.createBodyByCreate(idBody); }
        }

        if (!wbody.classList.contains('windowBody')) {wbody.classList.add('windowBody'); console.log('У тела окна должен быть установлен класс "windowBody"! Проверьте имя класса у тела с id = "'+idBody+'"!');}
        if (replaceContentBody !== undefined) wbody.innerHTML = replaceContentBody; 
        w.appendChild(wbody);
        w.querySelector(".windowBClose").addEventListener('click', function(e) {self.close(e.target)});
        return w;
    };

    this.open = function (idBody, arg_options) {

        // получение опций из аргументов
                arg_options = arg_options || {};

        // получение опций из тела окна
        if (arg_options['create_method'] == 'clone' && document.getElementById(idBody)) var wbody_options = document.getElementById(idBody).dataset;
        else var wbody_options = {};

        // установка опций
                var options = {};
                for (var name in self.default_options) {
                    if (arg_options[name] !== undefined) options[name] = arg_options[name]; // если есть опция в аргументе - используем
                    else if (wbody_options[name] !== undefined) options[name] = wbody_options[name];  // если есть опция в теле окна - используем
                    else options[name] = self.default_options[name]; // иначе используем значение по умолчанию
                }

        // false - бесконечное число раз; число - столько раз
        if (self.count_open[idBody] === undefined) self.count_open[idBody] = 0;
            if (options['max_count'] !== false && self.count_open[idBody] >= options['max_count']) {return false;}
            else {
                self.count_open[idBody] += 1;
            }
            self.count_open_total += 1;

        // создаём окно
        var w = self.createWindow(idBody, options['create_method'], options['body_content']);
        self.count_open_total += 1;
            w.id = self.count_open_total + w.id;
            self.arrData[w.id] = {};

        if (!options['add_title']) self.get_wtitle(w).style.display = 'none';

        // устанавливаем опцию заголовка
        //var wbody = self.get_wbody(w);
        //if (options['text_title'] === undefined && wbody.dataset.text_title !== undefined) options['text_title'] = wbody.dataset.text_title;
        //else options['text_title'] = 'Без названия';
        
            // добавляем событие drag'n'drop
            var wtitle = self.get_wtitle(w);
            DND(wtitle, {
                down: function(e, data) {
                            if (data['isSensorDisplay']) e = e.touches[0];
                // необязательны, без них курсор будет смещаться к углу захватываемого объекта
                            data['shiftX'] = e.clientX - _getCoords(data['wtitle']).left; 
                            data['shiftY'] = e.clientY - _getCoords(data['wtitle']).top;
                            if (self.zi) self.zi.lift(data['w'], 'top')
                },
                move: function(e, data) {
                        if (data['isSensorDisplay']) e = e.touches[0];
                        var left = e.clientX - data['shiftX'];
                        var top = e.clientY - data['shiftY'];
                        var screen_width  = document.documentElement.clientWidth-parseInt(getComputedStyle(data['wtitle']).width);
                        var screen_height = document.documentElement.clientHeight-parseInt(getComputedStyle(data['wtitle']).height);
                        if (left < 0) {left = 0;}
                        else if (left > screen_width) {left = screen_width; }
                        data['w'].style.left = left+ "px";
                        if (top < 0) {top = 0;}
                        else if (top > screen_height) {top = screen_height; }
                        data['w'].style.top = top + "px";
                },
                data: {
                        wtitle: self.get_wtitle(w),
                        w: w
                }
            })
            
            if (options['class_title']) self.get_wtitle(w).classList.add(options['class_title']);
            if (options['class_body']) self.get_wbody(w).classList.add(options['class_body']);
            if (options['class_window']) w.classList.add(options['class_window']);
            
            // добавляем на страничку ;)
            document.body.appendChild(w);
            //if (options['func_before_place']) options['func_before_place'](wnew, function() {calc_top(wnew, wbody);});
            self.wtitle_text(w, options['text_title']);

            self.show(w);
        self.calc_position(w, options['position']);

        if (options['add_sheet']) self.add_sheet(w);

            if (self.zi) self.zi.add(w, 'top');

            if (options['autoclose'] !== false) {
                setTimeout(function() {
                self.close(w);
            }, options['autoclose']);
            }

            return w;
        };

    this.add_sheet = function(w) {
        var div = document.createElement("div");
        div.className = 'windowSheet';
        div.style.background = '#fff';
        div.style.width = '100%';
        div.style.height = '2000px';
        div.style.opacity = '0.8';
        div.style.position = 'fixed';
        div.style.left = '0';
        div.style.top = '0';
        div.id = w.id + '_sheet';
        div.addEventListener('click', function(e) { self.close(w);});
        if (self.zi) {
            self.zi.add(div, 'top');
            self.zi.lift(w, 'top');
        } else {
            div.style.zIndex = '10';
        }
        document.body.insertBefore(div, w);        
    }

    this.close = function(child_el) {
        w = self.get_w(child_el);
        
        self.count_open[self.get_wbody(w).id] -= 1;
        self.count_open_total -= 1;
        
        self.del_data(w);
        
        var sheet = document.getElementById(w.id + '_sheet');
        if (sheet) {
            sheet.style.transition = 'opacity 0.4s';
            sheet.style.opacity = '0';
        }
        w.style.transition = 'opacity 0.4s';
        w.style.opacity = '0';
        
        /*//if (self.close_direction == 'left') {
        w.style.transition = 'left 0.5s';
        w.style.left = '-1000px';
        self.close_direction = 'right';
        //} else if (self.close_direction == 'right') {
        //   w.style.right = String((parseInt(screen.width) - parseInt(getComputedStyle(w).width))/2) + 'px';
        //  w.style.left = 'auto';
        //    w.style.transition = 'right 0.5s';
        //   w.style.right = '-1000px';
        //   self.close_direction = 'left';
        //}*/
        
        setTimeout(function() {
            if (self.zi) self.zi.remove(w);
                   if (sheet) sheet.remove();
                   w.remove();
        },400);
    };

     this.show = function(w) {
            w.style.display = "block";
        self.get_wbody(w).style.display = "block";
        };

        this.wtitle_text = function(w, textTitle) {
            var wtitle = w.getElementsByClassName('windowTitleText')[0];
            if (textTitle !== undefined) {wtitle.textContent = textTitle;}
            else {return wtitle.textContent;}
    };

        this.wbody_html = function(w, textHtml) {
            var wbody = self.get_wbody(w);
            if (textHtml !== undefined) {wbody.innerHTML = textHtml; self.calc_position(w, 'center');}
            else {return wbody.innerHTML;}
    };
        
        this.calc_position = function(w, position) {
                if (position == 'center') {

                    var w_height = parseInt(getComputedStyle(w).height);
                    var client_height = document.documentElement.clientHeight;
                    var top = (client_height - w_height) / 2;
                    top = top >= 0 ? top : 0 ;
                    w.style.top = top+"px";
                
                    var w_width = parseInt(getComputedStyle(w).width);
                    var client_width = document.documentElement.clientWidth;
                    var left = (client_width - w_width) / 2;
                    left = left >= 0 ? left : 0 ;
                    w.style.left = left+"px";

                } else if (position == 'random') {
            w.style.left = (5+Math.random()*6)+"%";
            w.style.top  = (30+Math.random()*3)+"%";
                }
        }
}

function AdvancedWindow() {
        Window.call(this);
        var self = this;
        
        this.open_by_api = function(api, options) {
                options = options || [];
                options['create_method'] = 'create';
                options['body_content'] = 'Загрузка...';
        options['data'] = options['data'] || {};
                var text_title = options['text_title'];
                var w = self.open(api, options);
                if (w === false) return;
                
                //get_tab_content('windows', api, {}, {backup:false, tag_content:w.get_wbody(w)});

/*      sets['data'] = data;
        sets['type'] = 'POST';
        $.ajax(this.mod_url + 'api.php', {
                type: 'POST',
                data: option['data']
        });*/

            RA_raw(api, options['data'], {
                    func_after_load: function(res) {
                        self.wbody_html(w, res['message']);
                        // приоритет заголовка: arg, server, tag, deafult
                        if (text_title === undefined) self.wtitle_text(w, res['title']);
                        if(options['func_success']) options['func_success']();
                        run_inserted_scripts(self.get_wbody(w));
                    },
                func_fatal: function(err_text) {
                    self.wbody_html(w, err_text);
                },
                url: options['url']
            });
        }
}

var W = new AdvancedWindow();

function Coords(el) {
        var self = this;

        this.x_page = function() {
                
        }
        this.x_window = function() {
                
        }
        this.x_screen = function() {
                
        }
        this.y_page = function() {
                
        }
        this.y_window = function() {
                
        }
        this.y_screen = function() {
                
        }
        this.height = function() {
                
        }
        this.width = function() {
                
        }

        this.page_height = function() {
                
        }
        this.page_width = function() {
                
        }
        this.screen_height = function() {
                return screen.height;
        }
        this.screen_width = function() {
                return screen.width;
        }
        this.window_height = function() {
                return screen.availHeight;
        }
        this.window_width = function() {
                return screen.availWidth;
        }
        
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

/* --------------------------------
 *           Gallery
 - *-------------------------------*/

"use strict"

class Gallery {
    
    constructor(links) {
        this.open = this.open.bind(this);
        this.change = this.change.bind(this);
        this.links = links;
        this.i = 0;
        
        for (let link of links) {
            link.addEventListener('click', this.open);
            link.dataset.i =this.i;
            this.i += 1;
        }
        
        this.temoplate = `
        <img class='main_image'>
        <br>
        <div class='buttons'>
        <input class='prev_img' type="button" value='<'>
        <input type="button" value='X' onclick="W.close(this);">
        <input class='next_img' type="button" value='>'>
        </div>
        `;
        
    }
    
    open(e) {
    e.preventDefault();
    let img = e.target;
    
    let w = W.open('_fm2', {add_sheet:true, create_method:'create', body_content: this.temoplate, position: 'none'});
    w.classList.add('gallery');
    
    W.get_wbody(w).addEventListener('click', function(e) {
    if (e.target.classList.contains('windowBody')) W.close(e.target)}
    );
    
    W.get_wbody(w).querySelector('.main_image').src = img.parentElement.href;
    this.i = parseInt(img.parentElement.dataset.i);
    W.get_wbody(w).querySelector('.next_img').addEventListener('click', this.change);
    W.get_wbody(w).querySelector('.prev_img').addEventListener('click', this.change);
    }
    
    change(e) {
    let btn = e.target;
    let main_img = W.get_w(btn).querySelector('.main_image');
    
    if (btn.className === 'next_img') {
        if (this.i === this.links.length-1) this.i = 0;
        else this.i += 1;
    } else if (btn.className === 'prev_img') {
        if (this.i === 0) this.i = this.links.length-1;
        else this.i -= 1;
    }
    main_img.src = this.links[this.i].href;
    }
    
    }