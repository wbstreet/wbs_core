function ListDown(settlement_id, sets) {
    let self = this;

    this.settlement_id = settlement_id;
    this.get_suggestion = undefined; // переопределить
    
    if (!sets['own_tag_inner']) {
        sets['tag'].innerHTML = `
        <input type="button" class="button_ss" value="Выбрать город">
        <input class='form_ss' type='text'>
        <div><div class="suggestions"></div></div>
        <input type="hidden" class='selected_settlement' name="settlement_id" value="">
        `;
    }

    sets['button_ss'] = sets['tag'].querySelector('.button_ss');
    sets['form_ss'] = sets['tag'].querySelector('.form_ss')
    sets['suggestions'] = sets['tag'].querySelector('.suggestions');
    sets['selected_settlement'] = sets['tag'].querySelector('.selected_settlement');

    sets['func_after_setSettlement'] = sets['func_after_setSettlement'] || function() {};

    sets['set_in_init'] = sets['set_in_init'] === undefined || sets['set_in_init'] === null ? true : sets['set_in_init'];

    sets['func_build_row_prepare'] = sets['func_build_row_prepare'] || function(sugs, i) {};

    sets['func_build_row_html'] = sets['func_build_row_html'] || function(sug) {
        let div = document.createElement('div'); 
        if (sug.id !== undefined) div.dataset.id = sug.id;
        if (sug.click !== undefined) div.addEventListener('click', sug.click);
        div.innerHTML = sug.title;
        return div;
    }

    sets['func_set_settlement'] = sets['func_set_settlement']  || function() {};

    this.sets = sets || [];

    this.build_row = function(sugs, i) {
        let sug = self.sets['func_build_row_prepare'](sugs, i);
        return self.sets['func_build_row_html'](sug);
    }

    this.build_sugs = function(sugs) {
        sets['suggestions'].innerHTML = self.first_result;

        for (let i=0; i < sugs.length; i++) {
            let div = self.build_row(sugs, i);
            sets['suggestions'].appendChild(div);
        }

        if(sugs.length === 0) {
            let div = document.createElement('div');
            div.textContent = 'не найдено...';
            sets['suggestions'].appendChild(div);
        }

        if (self.last_result !== '')
            sets['suggestions'].appendChild(self.build_row([self.last_result], 0));

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
            self.sets['button_ss'].addEventListener('click', self.show_form_ss)
            self.sets['suggestions'].style.display = 'none';
            self.sets['form_ss'].style.display = 'none'
        }, 250)
    }
    
    this.show_form_ss = function() {
        self.sets['form_ss'].style.display = 'block'
        this.removeEventListener('click', self.show_form_ss)
        self.sets['form_ss'].addEventListener('keyup', self.suggest_settlement, true)
        self.sets['form_ss'].addEventListener('blur', self.hide_form_ss)
        self.sets['form_ss'].focus()
        self.get_suggestion('', 10);
        self.sets['suggestions'].style.display = 'block'
    }

    this.init = function() {
        self.sets['button_ss'].addEventListener('click', self.show_form_ss)
        self.sets['form_ss'].placeholder = 'Введите город';
        self.sets['form_ss'].dataset.prev = '';
        self.sets['form_ss'].autocomplete = 'off';
        if (self.sets['set_in_init'] && self.settlement_id !== null && self.settlement_id !== undefined) self.sets['func_set_settlement'](self.settlement_id, self);
    }

}
