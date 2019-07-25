/*
 A *uthor: Polyakov Konstantin
 Licence: Domain Public
 
 Use it! But be very carefull :)
 */

function ListDownSettlement(settlement_id, sets) {
    ListDown.call(this, settlement_id, sets);
    let self = this;
    this.first_result = '';
    this.last_result = '';
    
    sets = this.sets;
    
    sets['func_set_settlement'] = function (settlement_id, self) {
        if (self.sets['selected_settlement']) self.sets['selected_settlement'].value = settlement_id;
        self.id2settlementObj(settlement_id, {'func_success': function(res){
            sets['button_ss'].value = self.obj2full_name(res['data']) + " (ИЗМЕНИТЬ)";
        }});
    }
    
    sets['level'] = sets['level'] || 'settlement';
    
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

    sets['func_build_row_prepare'] = function(sugs, i) {
        let sug = {
            title: self.obj2full_name(sugs[i]),
            click: function(){
                self.sets['func_set_settlement'](this.dataset.id, self);
                self.sets['func_after_setSettlement'](this.dataset.id, self);
            }
        };

        if (sets['level'] == 'settlement') sug.id = sugs[i]['settlement_id'];
        else if (sets['level'] == 'region') sug.id = sugs[i]['region_id'];

       return sug;
    }

    this.get_suggestion = function(text, count) {
        
        var sags_tag = sets['suggestions'];
        sags_tag.innerHTML = '<div>поиск...</div>';
        RequestAction('get_suggestion', undefined, {'text': text, 'count': count, 'level':sets['level']}, function() {
            if (this.readyState != 4) return;
                      
            var sugs;   
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
            
            self.build_sugs(sugs);

        });
    }
    
    this.init();
}
