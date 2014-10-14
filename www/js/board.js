if (typeof(BOARD_JS) == 'undefined') {
    if (typeof rt_path == 'undefined')
        alert('올바르지 않은 접근입니다.');
    
    var BOARD_JS = true;

    // 검색 리다이렉트
    function doSearch(f) {
        var stx = f.stx.value.replace(/(^\s*)|(\s*$)/g,'');
        if (stx.length < 2) {
            alert('2글자 이상으로 검색하십시오.');
            f.stx.focus();
            return false;
        }
        
        return true;
    }
    
    function resize(obj, w, h) {
        var imgsrc = obj.src.replace(/thumb\/[0-9]+px_/g, '')
          , size = "이미지 사이즈 : "+w+" x "+h
          , popup = window.open('', 'image_window', 'width='+w+', height='+h+', top=20,left=20,scrollbars=yes,status=no,resizable=no');

        popup.document.open(); 
        popup.document.write("<html><head><meta http-equiv='content-type' content='text/html; charset="+rt_charset+"'>")
        popup.document.write("<style>*{margin:0;padding:0;} body{width:"+w+"px;height:"+h+"px;overflow:auto;}</style>");
        popup.document.write("<title>"+size+"</title></head><body oncontextmenu='return false'>")
        popup.document.write("<img src=\""+imgsrc+"\" onclick='self.close()' style='cursor:pointer;'>")
        popup.document.write("</body></html>");
        popup.document.close(); 
    }
    
    // 댓글 출력
    if (typeof co_guest != 'undefined') {

        // 댓글 박스
        function comment_box(comment_id, work) {
            var    el_id;
            // 댓글 아이디가 넘어오면 답변, 수정
            if (comment_id)
                el_id = (work == 'c') ? 'reply_' + comment_id : 'edit_' + comment_id;
            else
                el_id = 'comment_box';
        
            if (save_before    != el_id) {
                if (save_before) {
                    document.getElementById(save_before).style.display = 'none';
                    document.getElementById(save_before).innerHTML = '';
                }

                document.getElementById(el_id).style.display = 'block';
                document.getElementById(el_id).innerHTML = save_html;
                // 댓글 수정
                if (work == 'cu') {
                    document.getElementById('co_content').value = document.getElementById('save_comment_' +    comment_id).value;
                    
                    if (document.getElementById('secret_comment_'+comment_id).value)
                        document.getElementById('co_secret').checked = true;
                    else
                        document.getElementById('co_secret').checked = false;
                }
        
                document.fviewcomment.comment_id.value = comment_id;
                document.fviewcomment.w.value = work;
        
                save_before = el_id;
                
                document.getElementById('cw_place').style.display = (comment_id) ? 'block' : 'none';
                document.getElementById('comment_reply').style.display = (comment_id && work == 'c') ? 'block' : 'none';
            }
            
            if (co_guest && work == 'c')
                $('#kcaptcha').click();
            
            var co_parent = $('#co_content').parent(); 
                co_parent.width(co_parent.parent().width()-110);
        }
        
        // 댓글 삭제
        function comment_del(idx) {
            var form = document.fviewcomment;

            post_send('_trans/board_comment/delete', {
                'bo_table': form.bo_table.value,
                'wr_id': form.wr_id.value,
                'comment_id': idx,
                'qstr': form.qstr.value
            }, true);
        }

        // 댓글 리스트
        function comment_list(page) {
            $.post(rt_path + '/_board/comment', {
                'bo_table': rt_bo_table,
                'wr_id': co_wr_id,
                'qstr': co_qstr,
                'page': page
            }, function(data) {
                $('#view_comment').html(data);

                if (co_guest) {
                    var btn_submit = document.getElementById('btn_submit');
                    btn_submit.style.display = 'none';
                    btn_submit.disabled = true;
                    
                    $('body').on('keyup', '#wr_key', function() {
                        var btn_submit = document.getElementById('btn_submit');
                        var kcaptcha = document.getElementById('kcaptcha');
                        
                        if (hex_md5(this.value) == md5_norobot_key) {
                            kcaptcha.style.display = 'none';
                            btn_submit.style.display = 'block';
                            btn_submit.disabled = false;
                        }
                        else {
                            kcaptcha.style.display = 'block';
                            btn_submit.style.display = 'none';
                            btn_submit.disabled = true;
                        }
                    });
                }
                
                save_before = '';
                save_html = document.getElementById('comment_box').innerHTML;
                comment_box('',    'c'); // 댓글 폼 출력
            });
        }

        // 초기 출력
        var save_before, save_html;
        comment_list(0);
    }
}