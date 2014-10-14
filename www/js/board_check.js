if (typeof(BOARD_CHECK_JS) == 'undefined') {
    if (typeof rt_path == 'undefined')
        alert('올바르지 않은 접근입니다.');
    
    var BOARD_CHECK_JS = true;
    
    $('#allcheck').click(function() {
        $("input[name='wr_id[]']", document.fboardlist).prop('checked', this.checked);
    });
    
    function check_confirm(str) {
        if ($("input[name='wr_id[]']:checked", document.fboardlist).length < 1) {
            alert(str + '할 자료를 하나 이상 선택하세요.');
            return false;
        }
        return true;
    }
    
    // 선택한 게시물 삭제
    function select_delete() {
        var f = document.fboardlist;
    
        str = '삭제';
        if (!check_confirm(str))
            return;
    
        if (!confirm('선택한 게시물을 정말 '+str+' 하시겠습니까?\n\n한번 '+str+'한 자료는 복구할 수 없습니다'))
            return;
    
        f.action = rt_path + '/_trans/board_write/delete';
        f.submit();
    }
    
    // 선택한 게시물 복사 및 이동
    function select_copy(sw) {
        var f = document.fboardlist
          , str = (sw == 'copy') ? '복사' : '이동';
                           
        if (!check_confirm(str))
            return;
    
        var sub_win = window.open('', 'mvcp', 'left=50, top=50, width=500, height=550, scrollbars=1');
    
        f.sw.value = sw;
        f.action = rt_path + '/_board/movecopy';
        f.target = 'mvcp';
        f.submit();
    }

    // 관리자 팝업
    function board_admin() {
        var f = document.fboardlist
          , win = window.open('', 'board_admin', 'left=50,top=50,width=616,height=650,scrollbars=1');

        f.target = 'board_admin';
        f.action = rt_path + '/_board/admin';
        f.submit();
    }
}