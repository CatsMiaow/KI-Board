if (typeof(ADMIN_JS) == 'undefined') {
    if (typeof rt_path == 'undefined')
        alert('올바르지 않은 접근입니다.');
	
	var ADMIN_JS = true;
	
	$('#allcheck').click(function() {
		var chk = $("input[name='chk[]']", document.form);
		if (this.checked)
			chk.attr('checked', true);
		else
			chk.attr('checked', false);
	});

	function slt_check(f, act) {
		var str = '';
		if (act.indexOf('update') != -1)
			str = '수정'; 
		else if (act.indexOf('delete') != -1) 
			str = '삭제';
		else
			return;

		if ($("input[name='chk[]']:checked", f).length < 1) {
	    	alert(str + "할 자료를 하나 이상 선택하세요.");
			return;
	    }
	
	    if (str == '삭제' && !confirm('선택한 자료를 정말 삭제 하시겠습니까?'))
			return;

		f.action = act;
		f.submit();
	}

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
}