if (typeof(CATEGORY_JS) == 'undefined') {
    if (typeof rt_path == 'undefined')
        alert('올바르지 않은 접근입니다.');
    
    var CATEGORY_JS = true;
    var is_ready = false;
    
    function currentCate(obj, vals) {
        var code, num, tcode, scode, sclen;
        
        for (var i=0; i<vals.length; i++) {
            code = (vals[i].indexOf('-') !== -1) ? vals[i].split('-') : vals[i].split('.');
            num = i + 1;
                        
            $('#'+obj+num+' > option[value="'+code[0]+'"]').prop('selected', true).change();
            if (typeof code[1] != 'undefined') {
                tcode = code[0] + '-';
                scode = code[1].match(/[0-9]{3}/g);
                sclen = scode.length;
                for (var j=0; j<sclen; j++) {
                    tcode = tcode + scode[j];            
                    $('#'+obj+num+'_'+(j+1)+' > option[value='+tcode+']').prop('selected', true).change();
                }
            }
        }
        is_ready = true;
    }

    function changeCate(c, lo) {
        c = $(c);
        var cval = c.val();

        if (is_ready && lo) {
            if (!cval)
                ca_url = ca_url.replace(/(\/[a-z]+\/)$/,'');
            document.location.replace(rt_path + '/' + ca_url + cval.replace('-', '.')); 
            return false;
        }
        
        var cpar = c.parent();
        var parid = cpar.find('select:first').attr('id');
        var cname = cpar.find("select[name!='']:last").attr('name');
        
        if (!cval) { // <span></span> or ,:hidden
            var temp = c.attr('id').match(/_([0-9])+/);
            if (!temp)
                cpar.find("select:not(:first)").remove();
            else
                cpar.find("select:not(:lt("+(parseInt(temp[1]) + 1)+"))").remove();
            cpar.find("select:last").attr('name', cname);
            return false;
        }

        var cvals = cval.split('-');
        var cvalLen = (cvals[1] ? cvals[1].length : 0) + 3;

        var i = 0;
        var options = new Array();
        
        $('#sub_'+parid.replace(/[0-9]+/g,'')+" > option[value^='"+cval+"']").each(function() {
            if (this.value.replace(cvals[0]+'-', '').length == cvalLen) {
                options[i] = this;
                i++;
            }
        });

        if (!options.length || c.next().is('select')) {
            var ltVal = cvalLen / 3;
            cpar.find("select:not(:lt("+ltVal+"))").remove();
            cpar.find("select:last").attr('name', cname);
            if (options.length < 1)
                return false;
        }

        var sid = (typeof cvals[1] == 'undefined') ? parid+'_1' : parid+'_'+(cvals[1].length/3+1);
        
        if (document.getElementById(sid) == null) {
            var first = (lo) ? "<option value='"+cval+"'>전체</option>" : "<option value=''>선택하세요</option>";
            cpar.find('select').removeAttr('name');
            c.after("<select id='"+sid+"' class='form-control input-sm auto' name='"+cname+"' onchange='javascript:changeCate(this, "+lo+");'>"+first+"</select>");
        }
        else
            $('#'+sid).find("option[value!='']").remove();

        $(options).clone().appendTo($('#'+sid));
        document.getElementById(sid).options[0].selected = true;
    }
}