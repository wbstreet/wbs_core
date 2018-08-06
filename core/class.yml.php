<?php

class MyXMLWriter extends XMLWriter {

    function addAttributes($atttrs) {
        foreach($atttrs as  $name => $value) {
            $this->startAttribute($name);
            if ($value !== null) $this->text($value);
            $this->endAttribute();
        }
    }
    
    function addElement($name, $text=null, $attributes=null, $end=true) {
        $this->startElement($name);
        if ($attributes) $this->addAttributes($attributes);
        if ($text !== null) $this->text($text);
        if ($end) $this->endElement();
    }
}

# Требования к YML https://yandex.ru/support/partnermarket/export/yml.html#text-csv

class WbsYML {
    
    function __construct($filename) {
        $this->filename = $filename;
        $this->xw = new MyXMLWriter();
        $this->xw->openMemory();
        
        $this->xw->startDocument("1.0", "UTF-8");
        $this->xw->addElement("yml_catalog", "example", ["date"=>"ttt"], false);
    }

    function replace_special_symbols($orig_str) {
        return str_replace(['&','"', '>', '<', "'"], ['&amp;', '&quot;', '&gt;', '&lt;', '&apos;'], $orig_str);
    }

    // Розничная торговля, другой бизнес: фид Яндекс.Маркета
    function startOfferMarket($id, $available, $name, $url, $picture, $price, $currencyId, $categoryId, $additional=null) {
        $this->xw->addElement("offer", null, ['id'=>$id, 'available'=>$available], false);
        
        $this->xw->addElement("name", $name);
        $this->xw->addElement("picture", $picture);
        $this->xw->addElement("url", $url);
        $this->xw->addElement("price", $price);
        $this->xw->addElement("currencyId", $currencyId);
        $this->xw->addElement("categoryId", $categoryId);
        
        if ($additional) foreach($additional as $n => $v) $this->xw->addElement($n, $v);
    }

    // Недвижимость: фид Яндекс.Недвижимости
    function startOfferAppartment() {
    }

    function endOffer() {
        $this->xw->endElement();
    }

    /* shop: https://yandex.ru/support/partnermarket/elements/shop.html
     currencies: https://yandex.ru/support/partnermarket/currencies.html
         ['id'='RUB', 'rate'='CB', 'plus'='3']
     categories: https://yandex.ru/support/partnermarket/categories.html
         ['id'='1', 'parentId'='1', 'name'='']
    */
    function startShop($name, $company, $url, $currencies, $categories, $additional=null) {
        $this->xw->addElement("shop", null, null, false);

        $this->xw->addElement("name", $name);
        $this->xw->addElement("company", $company);
        $this->xw->addElement("url", $url);
        if ($additional) foreach($additional as $n => $v) $this->xw->addElement($n, $v);

        $this->xw->addElement("currencies", null, null, false);
        foreach($currencies as $i => $v) $this->xw->addElement("currency", null, $v);
        $this->xw->endElement();
        
        $this->xw->addElement("categories", null, null, false);
        foreach($categories as $i => $v) {
            $cat_name = $v['name'];
            unset($v['name']);
            $this->xw->addElement("category", $cat_name, $v);
        }
        $this->xw->endElement();
        
        $this->xw->addElement("offers", null, null, false);
    }

    function endShop() {
        $this->xw->endElement(); // close offers
        $this->xw->endElement(); // close shop
    }

    function write() {
        $this->xw->endElement(); // close <yml_catalog>
        $this->xw->endDocument();

        echo $this->xw->outputMemory();
    }
}

if (isset($_GET['main_yml'])) {
    $clsYml = new WbsYML('test.xml', 'market');
    $clsYml->startShop("Магазин Косметик", "ООО Петросян", "syeys.ru", [['id'=>'RUB', 'rate'=>'CB']], [['id'=>'1', 'name'=>'Цветы'], ['id'=>'1', 'name'=>'Деревья']]);
    $clsYml->startOfferMarket('4', 'true', 'Хороший товар', 'https://syeysk.ru/good/1', 'https://syeysk.ru/pic/1/1', '101', 'RUB', '56');
    $clsYml->endOffer();
    $clsYml->endShop();
    $clsYml->write();
}