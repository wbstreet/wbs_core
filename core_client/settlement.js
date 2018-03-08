/*
 A *uthor: Polyakov Konstantin
 Licence: Domain Public
 
 Use it! But be very carefull :)
 */

function Settlement(settlement_id, sets) {
    var self = this;
    this.settlement_id = settlement_id;
    this.first_result = '';
    this.last_result = '';
    
    sets = sets || [];
    
    sets['name'] = sets['name'] || 'settlement_id';
    
    if (!sets['own_tag_inner']) {
        sets['tag'].innerHTML = `
        <input type="button" class="button_ss" value="Выбрать город">
        <input class='form_ss' type='text'>
        <div><div class="suggestions"></div></div>
        <input type="hidden" class='selected_settlement' name="`+sets['name']+`" value="" onchange="`+sets['onchange']+`">
        `;
    }
    sets['button_ss'] = sets['tag'].querySelector('.button_ss');
    sets['form_ss'] = sets['tag'].querySelector('.form_ss')
    sets['suggestions'] = sets['tag'].querySelector('.suggestions');
    sets['selected_settlement'] = sets['tag'].querySelector('.selected_settlement');
    
    sets['func_setSettlement'] = sets['func_setSettlement'] || function (settlement_id, self) {
        if (self.sets['selected_settlement']) self.sets['selected_settlement'].value = settlement_id;
        self.id2settlementObj(settlement_id, {'func_success': function(res){
            sets['button_ss'].value = self.obj2full_name(res['data']) + " (ИЗМЕНИТЬ)";
        }});
    }
    sets['func_after_setSettlement'] = sets['func_after_setSettlement'] || function() {};
    
    sets['level'] = sets['level'] || 'settlement';
    sets['set_in_init'] = sets['set_in_init'] === undefined || sets['set_in_init'] === null ? true : sets['set_in_init'];
    
    self.sets = sets;
    
    // по умолчанию - сохраняет город в куках
    this.setSettlement = sets['func_setSettlement'];
    
    this.obj2full_name = function(obj, is_hide_region) {
        if (obj === null || obj === undefined || obj.length === 0) {return 'Не выбран. Нажмите для выбора';}
        var full_name = '';
        if (sets['level']=='settlement') {
            full_name = obj['type_short_name'] +". "+ obj['settlement_name'];
            if (!is_hide_region) full_name += ", "+ obj['region_name'];
        }
        else if (sets['level']=='region') full_name = obj['region_name'] +", "+ obj['country_name'];
        return full_name;
    }
    
    this.id2settlementObj = function(id, options) {
        if (id === undefined || id === null) return [];
        if (options.func_error === undefined) options.func_error =  function(res) {console.log(res['message'])};
        var req = RA_raw('get_settlement', {'id': id, 'level':sets['level']}, options);
    }
    
    this.get_suggestion = function(text, count) {
        function get_row(textContent, id, click) {
            var div = document.createElement('div'); 
            if (id !== undefined) div.dataset.id = id;
            if (click !== undefined) div.addEventListener('click', click);
            div.innerHTML = textContent;
            return div;
        }
        
        var sags_tag = sets['suggestions'];
        sags_tag.innerHTML = '<div>поиск...</div>';
        RequestAction('get_suggestion', undefined, {'text': text, 'count': count, 'level':sets['level']}, function() {
            if (this.readyState != 4) return;
                      
                      var sugs, id;   
            if (this.status==200) {
                var res =  JSON.parse(this.responseText);
                if (res['success'] == 1) sugs = res['data'];
                      else {
                          sugs = [{'settlement_id':0, 'settlement_name': 'Error server',
                              'region_id':0, 'region_name': 'Error server'
                          }];
                          console.log(res['message']);
                      }
            } else {sugs = [{'settlement_id':0, 'settlement_name': 'Error connection'}];}
            
            sags_tag.innerHTML = self.first_result;
            for (var i=0; i < sugs.length; i++) {
                if (sets['level'] == 'settlement') id = sugs[i]['settlement_id'];
                      else if (sets['level'] == 'region') id = sugs[i]['region_id'];
                      var div = get_row(self.obj2full_name(sugs[i]), id, function(){self.setSettlement(this.dataset.id, self);self.sets['func_after_setSettlement'](this.dataset.id, self);});
                sags_tag.appendChild(div);
            }
            if(sugs.length === 0) {
                var div = document.createElement('div'); div.textContent = 'не найдено...';
                sags_tag.appendChild(div);// = '<div>не найдено...</div>';
            }
            if (self.last_result !== '') sags_tag.appendChild(get_row(self.last_result));
        });
    }
    
    this.suggest_settlement = function() {
        if (this.value == this.dataset.prev) { return;}
        if (this.value == ' ') this.value = this.value.trim();
        //if (this.value.length == 0) return;
        //var pattern = new RegExp('.*'+this.dataset.default+'.*');
        //if (this.value.search(pattern) > -1) {this.value = this.value.replace(this.dataset.default, ''); return;}
        self.get_suggestion(this.value, 10);
        this.dataset.prev = this.value;
    }
    
    this.hide_form_ss = function() {
        setTimeout(function() { // чтобы сначала отработало событие click на выбранном пользователе населённном пункте
            sets['button_ss'].addEventListener('click', self.show_form_ss)
            sets['suggestions'].style.display = 'none';
            sets['form_ss'].style.display = 'none'
        }, 250)
    }
    
    this.show_form_ss = function() {
        sets['form_ss'].style.display = 'block'
        this.removeEventListener('click', self.show_form_ss)
        sets['form_ss'].addEventListener('keyup', self.suggest_settlement, true)
        sets['form_ss'].addEventListener('blur', self.hide_form_ss)
        sets['form_ss'].focus()
        self.get_suggestion('', 10);
        sets['suggestions'].style.display = 'block'
    }
    
    function init() {
        self.sets['button_ss'].addEventListener('click', self.show_form_ss)
        self.sets['form_ss'].placeholder = 'Введите город';
        self.sets['form_ss'].dataset.prev = '';
        self.sets['form_ss'].autocomplete = 'off';
        if (self.sets['set_in_init'] && self.settlement_id !== null && self.settlement_id !== undefined) self.setSettlement(self.settlement_id, self);
    }
    
    init();
}