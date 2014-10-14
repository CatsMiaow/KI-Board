if (typeof(EDITOR_JS) == 'undefined') {
    var EDITOR_JS = true;

    // editorConfig, editorNumber Required
    var config = { // 기본 설정 값
        form: 'feditor',
        pvpage: '#host#path/pages/pv/#pvname.html',
        canvas: {
        },
        events: {
            preventUnload: false
        },
        sidebar: {
            attachbox: {
                show: true
            },
            embeder: {
                media: { popPageUrl: rt_path + '/board/' + rt_bo_table + '/editor/type/media' }
            },
            attacher: {
                image: {
                    features: { left:10, top:10, width:550, height:400 },
                    popPageUrl: rt_path + '/board/' + rt_bo_table + '/editor/type/image',
                    checksize: true
                },
                file: {
                    features: { left:10, top:10, width:550, height:400 },
                    popPageUrl: rt_path + '/board/' + rt_bo_table + '/editor/type/file'
                }
            },
            capacity: {
                maximum: 5242880
            }
        },
        plugin: {
            fullscreen: {
                use: true
            }
        },
        txHost: '', // http://xxx.xxx.com
        txPath: rt_path + '/editor/',
        wrapper: 'tx_trex_container',
        txIconPath: rt_path + '/editor/images/icon/editor/',
        txDecoPath: rt_path + '/editor/images/deco/contents/',
        initializedId: ''
    };


    EditorJSLoader.ready(function(Editor) {
        var editorExecute = function(editorConfig) {
            for (idx in editorConfig) {
                var exConfig = $.extend({}, config, editorConfig[idx]); // 기본+사용자 설정

                new Editor(exConfig);
                Editor.getCanvas().observeJob(Trex.Ev.__IFRAME_LOAD_COMPLETE, function() {
                    Editor.modify({
                        'content': exConfig.content, // 내용 입력
                        'attachments': function() { // 파일 추가
                            var allattachments = [];
                            for (var i in exConfig.attachments) {
                                allattachments = allattachments.concat(exConfig.attachments[i]);
                            }
                            return allattachments;
                        }()
                    });
                    editorNumber.push(exConfig.initializedId); // 에디터 넘버링 보관
                    delete editorConfig[idx]; // 처리한 설정 삭제
                    editorExecute(editorConfig); // 재실행
                });
                break;
            }
        }
        editorExecute(editorConfig);
    });

    // Editor.switchEditor(initializedId); // 에디터 전환, initializedId 설정 값
    // Editor.getCanvas().pasteContent('내용'); // 내용 덧붙이기
    // Editor.getContent(); // 내용 가져오기


    function validForm(editor) {
        // var validator = new Trex.Validator()
        //   , content   = editor.getContent();
        if (!new Trex.Validator().exists(editor.getContent())) {
            alertMsg('내용을 입력하세요.');
            return false;
        }

        $('#write_submit').button('loading');
        return true;
    }

    // 폼 필드(값) 세팅 
    function setForm(editor) {
        var _formGen = editor.getForm();
    
        // 본문 필드
        _formGen.createField(
            tx.textarea({
                'name': editor.initialConfig.field,
                'style': { 'display':'none' }
            }, editor.getContent())
        );

        // 이미지 첨부 필드
        var _images = editor.getAttachments('image');
        for(var i=0, len=_images.length; i<len; i++) {
            if (_images[i].existStage) {
                _formGen.createField(
                    tx.input({ 
                        'type': 'hidden', 
                        'name': 'images[]',
                        'value': _images[i].data.imageurl.match(/([0-9]{10}_)?[a-z0-9]{32}\.[a-z]{3}/i)[0]
                    })
                );
                _formGen.createField(
                    tx.input({ 
                        'type': 'hidden', 
                        'name': 'inames[]',
                        'value': _images[i].data.filename
                    })
                );
            }
        }
        
        // 파일 첨부 필드
        var _files = editor.getAttachments('file');
        for(var i=0, len=_files.length; i<len; i++) {
            if (_files[i].existStage) {
                var fileVal = _files[i].data.attachurl.match(/([0-9]{10}_)?[a-z0-9]{32}\.[a-z]{3}/i);
                if (fileVal == null)
                    fileVal = _files[i].data.attachurl.match(/[0-9]+$/);

                _formGen.createField(
                    tx.input({ 
                        'type': 'hidden',
                        'name': 'files[]', 
                        'value': fileVal[0]
                    })
                );
                _formGen.createField(
                    tx.input({ 
                        'type': 'hidden',
                        'name': 'fnames[]', 
                        'value': _files[i].data.filename
                    })
                );
            }
        }

        return true;
    }

    // SyntaxHighlighter
    function codeSyntax(c) {
        if (!c.value) return false;

        Editor.getCanvas().pasteContent(
            '<pre class="brush: '+c.value+'" style="border:1px dashed rgb(203, 203, 203); background-color:rgb(255,255,255); padding:10px;">'
            + '<p>Insert `'+c.options[c.selectedIndex].innerHTML+'` Source Code</p>'
            + '</pre>'
        );

        c.value = '';
    }

}