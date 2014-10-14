if (typeof(SIDEVIEW_JS) == 'undefined') {
    if (typeof rt_path == 'undefined')
        alert('올바르지 않은 접근입니다.');

    var SIDEVIEW_JS = true;

    // 아래의 소스코드는 daum.net 카페의 자바스크립트를 참고
    // 회원이름 클릭시 회원정보등을 보여주는 레이어
    function insertHead(name, text, evt) {
        var idx = this.heads.length;
        var row = new SideViewRow(-idx, name, text, evt);
        this.heads[idx] = row;
        return row;
    }

    function insertTail(name, evt) {
        var idx = this.tails.length;
        var row = new SideViewRow(idx, name, evt);
        this.tails[idx] = row;
        return row;
    }

    function SideViewRow(idx, name, onclickEvent) {
        this.idx = idx;
        this.name = name;
        this.onclickEvent = onclickEvent;
        this.renderRow = renderRow;
        
        this.isVisible = true;
        this.isDim = false;
    }

    function renderRow() {
        if (!this.isVisible)
            return "";
        
        // var str = "<li id='sideViewRow_"+this.name+"'>"+this.onclickEvent+"</li>";
        var str = '<a id="sideViewRow_'+this.name+'" class="list-group-item panel-success" '+this.onclickEvent+'</a>';
        return str;
    }

    function showSideView(curObj, mb_id, name) {
        var sideView = new SideView('sideview', curObj, mb_id, name);
        sideView.showLayer();
    }

    function SideView(targetObj, curObj, mb_id, name) {
        this.targetObj = targetObj;
        this.curObj = curObj;
        this.mb_id = mb_id;
        name = name.replace(/…/g,"");
        this.name = name;
        this.showLayer = showLayer;
        this.makeNameContextMenus = makeNameContextMenus;
        this.heads = new Array();
        this.insertHead = insertHead;
        this.tails = new Array();
        this.insertTail = insertTail;
        this.getRow = getRow;
        this.hideRow = hideRow;
        this.dimRow = dimRow;
    
        // 쪽지보내기 & 자기소개
        if (mb_id) {
            this.insertTail("info", "href='javascript:;' onclick=\"win_profile('"+mb_id+"');\"><span class='glyphicon glyphicon-home'></span> 자기소개");
            this.insertTail("memo", "href='javascript:;' onclick=\"win_memo('member/memo/write/"+mb_id+"');\"><span class='glyphicon glyphicon-comment'></span> 쪽지보내기");
        }
        
        // 게시판테이블 아이디가 넘어왔을 경우
        if (typeof rt_bo_table != 'undefined') {
            var sca_str = (rt_bo_sca) ? '&sca=' + rt_bo_sca : '';
            if (mb_id) // 회원일 경우 아이디로 검색
                this.insertTail("mb_id", "href='"+rt_path+"/board/"+rt_bo_table+"/lists?"+sca_str+"&sfl=mb_id&stx="+mb_id+"'><span class='glyphicon glyphicon-search'></span> 아이디로 검색");
            else // 비회원일 경우 이름으로 검색
                this.insertTail("name", "href='"+rt_path+"/board/"+rt_bo_table+"/lists?"+sca_str+"&sfl=wr_name&stx="+name+"'><span class='glyphicon glyphicon-search'></span> 이름으로 검색");
        }

        // 최고관리자일 경우
        if (typeof rt_admin != 'undefined') {
            if (mb_id) {
                // 회원정보변경
                this.insertTail("modify", "href='"+rt_path+"/" + rt_admin + "/member/form/u/"+mb_id+"' target='_blank'><span class='glyphicon glyphicon-edit'></span> 회원정보변경");
                // 포인트내역
                this.insertTail("point", "href='"+rt_path+"/" + rt_admin + "/point/lists?sfl=mb_id&stx="+mb_id+"' target='_blank'><span class='glyphicon glyphicon-barcode'></span> 포인트내역");            
            }               
        }
    }

    function showLayer() {
        clickAreaCheck = true;
        var oSideViewLayer = document.getElementById(this.targetObj);
        var oBody = document.body;
            
        if (oSideViewLayer == null) {
            oSideViewLayer = document.createElement("DIV");
            oSideViewLayer.id = this.targetObj;
            // oSideViewLayer.className = 'well';
            oSideViewLayer.style.position = 'absolute';
            oBody.appendChild(oSideViewLayer);
        }
        oSideViewLayer.innerHTML = this.makeNameContextMenus();
        
        if (getAbsoluteTop(this.curObj) + this.curObj.offsetHeight + oSideViewLayer.scrollHeight + 5 > oBody.scrollHeight)
            oSideViewLayer.style.top = (getAbsoluteTop(this.curObj) - oSideViewLayer.scrollHeight)+'px';
        else
            oSideViewLayer.style.top = (getAbsoluteTop(this.curObj) + this.curObj.offsetHeight)+'px';
        
        oSideViewLayer.style.left = (getAbsoluteLeft(this.curObj) - this.curObj.offsetWidth + 14)+'px';

        divDisplay(this.targetObj, 'block');
    }

    function getAbsoluteTop(oNode) {
        var oCurrentNode=oNode;
        var iTop=0;
        while(oCurrentNode.tagName!="BODY") {
            iTop+=oCurrentNode.offsetTop - oCurrentNode.scrollTop;
            oCurrentNode=oCurrentNode.offsetParent;
        }
        return iTop;
    }

    function getAbsoluteLeft(oNode) {
        var oCurrentNode=oNode;
        var iLeft=0;
        iLeft+=oCurrentNode.offsetWidth;
        while(oCurrentNode.tagName!="BODY") {
            iLeft+=oCurrentNode.offsetLeft;
            oCurrentNode=oCurrentNode.offsetParent;
        }
        return iLeft;
    }

    function makeNameContextMenus() {
        var str = '<div class="list-group">';
        
        var i=0;
        for (i=this.heads.length - 1; i >= 0; i--)
            str += this.heads[i].renderRow();
       
        var j=0;
        for (j=0; j < this.tails.length; j++)
            str += this.tails[j].renderRow();
        
        str += "</div>";
        return str;
    }

    function getRow(name) {
        var i = 0;
        var row = null;
        for (i=0; i<this.heads.length; ++i) 
        {
            row = this.heads[i];
            if (row.name == name) return row;
        }

        for (i=0; i<this.tails.length; ++i) 
        {
            row = this.tails[i];
            if (row.name == name) return row;
        }
        return row;
    }

    function hideRow(name) {
        var row = this.getRow(name);
        if (row != null)
            row.isVisible = false;
    }

    function dimRow(name) {
        var row = this.getRow(name);
        if (row != null)
            row.isDim = true;
    }
    // Internet Explorer에서 셀렉트박스와 레이어가 겹칠시 레이어가 셀렉트 박스 뒤로 숨는 현상을 해결하는 함수
    // 레이어가 셀렉트 박스를 침범하면 셀렉트 박스를 hidden 시킴
    // <div id=LayerID style="display:none; position:absolute;" onpropertychange="selectBoxHidden('LayerID')">
    function selectBoxHidden(layer_id) {
        // var ly = eval(layer_id);
        var ly = document.getElementById(layer_id);

        // 레이어 좌표
        var ly_left   = ly.offsetLeft;
        var ly_top    = ly.offsetTop;
        var ly_right  = ly.offsetLeft + ly.offsetWidth;
        var ly_bottom = ly.offsetTop + ly.offsetHeight;

        // 셀렉트박스의 좌표
        var el;

        for (i=0; i<document.forms.length; i++) {
            for (k=0; k<document.forms[i].length; k++) {
                el = document.forms[i].elements[k];    
                if (el.type == "select-one") {
                    var el_left = el_top = 0;
                    var obj = el;
                    if (obj.offsetParent) {
                        while (obj.offsetParent) {
                            el_left += obj.offsetLeft;
                            el_top  += obj.offsetTop;
                            obj = obj.offsetParent;
                        }
                    }
                    el_left   += el.clientLeft;
                    el_top    += el.clientTop;
                    el_right  = el_left + el.clientWidth;
                    el_bottom = el_top + el.clientHeight;

                    // 좌표를 따져 레이어가 셀렉트 박스를 침범했으면 셀렉트 박스를 hidden 시킴
                    if ( (el_left >= ly_left && el_top >= ly_top && el_left <= ly_right && el_top <= ly_bottom) || 
                         (el_right >= ly_left && el_right <= ly_right && el_top >= ly_top && el_top <= ly_bottom) ||
                         (el_left >= ly_left && el_bottom >= ly_top && el_right <= ly_right && el_bottom <= ly_bottom) ||
                         (el_left >= ly_left && el_left <= ly_right && el_bottom >= ly_top && el_bottom <= ly_bottom) ||
                         (el_top <= ly_bottom && el_left <= ly_left && el_right >= ly_right)
                        )
                        el.style.visibility = 'hidden';
                }
            }
        }
    }

    // 감추어진 셀렉트 박스를 모두 보이게 함
    function selectBoxVisible() {
        for (i=0; i<document.forms.length; i++) {
            for (k=0; k<document.forms[i].length; k++) {
                el = document.forms[i].elements[k];    
                if (el.type == "select-one" && el.style.visibility == 'hidden')
                    el.style.visibility = 'visible';
            }
        }
    }

    function divDisplay(id, act) {
        selectBoxVisible();

        document.getElementById(id).style.display = act;
    }

    function hideSideView() {
        if (document.getElementById("sideview"))
            divDisplay ("sideview", 'none');
    }

    var clickAreaCheck = false;
    document.onclick = function() {
        if (!clickAreaCheck) 
            hideSideView();
        else 
            clickAreaCheck = false;
    }
}