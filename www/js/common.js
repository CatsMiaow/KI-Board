if (typeof(COMMON_JS) == 'undefined') {
	var COMMON_JS = true;

    /*
     * checkbox를 아이콘( http://getbootstrap.com/components/#glyphicons )으로
     * <div class="btn-group" data-toggle="buttons">
     *     <label id="open" class="btn btn-sm btn-default">
     *         <input type="checkbox" name="open" value="1" /> <span class="glyphicon glyphicon-unchecked"></span>
     *     </label>
     * </div>
     * $('#open').checkicon(<?=$open?>, 'unchecked', 'check');
     */
    jQuery.fn.checkicon = function(init, unchecked, check) {
        if (!unchecked) unchecked = 'unchecked';
        if (!check) check = 'check';

        $(this).find('> input:checkbox').change(function() {
            var icon = $(this).prop('checked') ? check : unchecked;
            $(this).next('span').prop('class', 'glyphicon glyphicon-' + icon);

            try { // jQuery Validate 처리, rules에 포함되지 않은 field는 valid 시 오류가 발생한다.
                $(this).rules(); // rules 실행 시 오류가 없으면 rules에 포함된 것
                $(this).valid(); // 으로 간주하여 valid를 실행
            } catch(e) { }
        });
        // init checked
        if (parseInt(init)) $(this).button('toggle');
    }

    /*
     * <div id="options" class="btn-group" data-toggle="buttons">
     *     <label class="btn btn-default">
     *         <input type="radio" name="options" value="1" /> <span class="glyphicon glyphicon-unchecked"></span> Option1
     *     </label>
     *     <label class="btn btn-default">
     *         <input type="radio" name="options" value="2" /> <span class="glyphicon glyphicon-unchecked"></span> Option2
     *     </label>
     *     <label class="btn btn-default">
     *         <input type="radio" name="options" value="3" /> <span class="glyphicon glyphicon-unchecked"></span> Option3
     *     </label>
     * </div>
     * $('#options').radioicon('<?=$value?>');
     */
    jQuery.fn.radioicon = function(init, unchecked, check) {
        if (!unchecked) unchecked = 'unchecked';
        if (!check) check = 'check';

        var options = $(this);
        options.find('> label').click(function() {
            options.find('> label > span').prop('class', 'glyphicon glyphicon-' + unchecked);
            $(this).find('> span').prop('class', 'glyphicon glyphicon-' + check);
        });

        // init checked
        if (init) options.find('> label > input[value="'+init+'"]').parent().click();
    }

    /*
     * 레이어 알림 메시지
     * http://getbootstrap.com/components/#alerts
     * http://getbootstrap.com/javascript/#alerts
     */
    function alertMsg(msg, color) {
        if (!color) color = 'danger'; // success,info,warning,danger

        var li = $(
            '<li class="alert alert-'+color+' fade in" style="margin-bottom:10px;">'
            + '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'
            + msg
            + '</li>'
        );

        $('#alert').prepend(li);
        setTimeout(function() {
            li.alert('close');
        }, 3000);
    }
    

    // 팝업 창
    function win_open(url, name, option) {
        var popup = window.open(rt_path + '/' + url, name, option);
        popup.focus();
    }

    // 쪽지 창
    function win_memo(url) {
		if (!url) url = "member/memo/lists";
        win_open(url, "winMemo", "left=50,top=50,width=616,height=460,scrollbars=1");
    }
    
	// 자기소개 창
    function win_profile(mb_id) {
        win_open("member/profile/qry/"+mb_id, 'winProfile', 'left=50,top=50,width=400,height=500,scrollbars=1');
    }

    // 우편번호 창
    function win_zip(frm_name, frm_zip1, frm_zip2, frm_addr1, frm_addr2) {
        url = "useful/zip/qry/"+frm_name+"/"+frm_zip1+"/"+frm_zip2+"/"+frm_addr1+"/"+frm_addr2;
        win_open(url, "winZip", "left=50,top=50,width=616,height=460,scrollbars=1");
    }

	// POST 전송, 결과값 리턴
    function post_send(href, parm, del) {
        if (!del || confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) { 
			$.post(rt_path + '/' + href, parm, function(req) {
                document.write(req);
			});
		}
    }
    
    // POST 이동
    function post_goto(url, parm, target) {
        var f = document.createElement('form');
        
        var objs, value;
        for (var key in parm) {
            value = parm[key];
            objs = document.createElement('input');
            objs.setAttribute('type', 'hidden');
            objs.setAttribute('name', key);
            objs.setAttribute('value', value);
            f.appendChild(objs);
        }
        
        if (target)
            f.setAttribute('target', target);

        f.setAttribute('method', 'post');
        f.setAttribute('action', rt_path + '/' + url);
        document.body.appendChild(f);
        f.submit();
    }
    
    // POST 창
    function post_win(name, url, parm, opt) {
        var temp_win = window.open('', name, opt);
            post_goto(url, parm, name);
    }

    // 일반 삭제 검사 확인
    function del(href) {
        if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) 
            document.location.href = rt_path + '/' + href;
    }
    
    // script 에서 js 파일 로드
    function importScript(FILES) {
        var _importScript = function(filename) { 
        	if (filename) {
        		document.write('<script type="text/javascript" src="'+rt_path+'/js/'+filename+'.js"></s'+'cript>');
            }
        };
        
        for (var i=0; i<FILES.length; i++) {
        	_importScript(FILES[i]);
        }
    }
    
    // jQuery textarea
    function txresize(tx, type, size) {
        var tx = $('#'+tx);
        if (type == 1)
            tx.animate({'height':'-='+size+'px'}, 'fast');
        else if (type == 2)
            tx.animate({'height':size}, 'fast');
        else if (type == 3)
            tx.animate({'height':'+='+size+'px'}, 'fast');
    }

    // 팝업 닫기
    function popup_close(id, onday) {
    	if (onday) {
    		var today = new Date();
    		today.setTime(today.getTime() + (60*60*1000*24));
    		document.cookie = id + "=" + escape( true ) + "; path=/; expires=" + today.toGMTString() + ";";
    	}

    	if (window.parent.name.indexOf(id) != -1)
    		window.close();
    	else
    		document.getElementById(id).style.display = 'none';
    }
}