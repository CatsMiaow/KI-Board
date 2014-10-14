if (typeof(VALID_REG_JS) == 'undefined') {
    if (typeof rt_path == 'undefined')
        alert('올바르지 않은 접근입니다.');

    var VALID_REG_JS = true;

    var mb_id_enabled = ''
      , mb_nick_enabled = ''
      , mb_email_enabled = '';

    $(function() {
        $('#btn_id').click(function() {
            var btn = $(this);
                btn.button('loading');

            $('#msg_mb_id').load(rt_path + '/_trans/register/id', {
                'reg_mb_id': $('#reg_mb_id').val()
            }, function(req) {
                mb_id_enabled = $('#reg_mb_id').val();
                $('#reg_mb_id').valid();
                btn.button('reset');
            });
        });

        $('#btn_nick').click(function() {
            var btn = $(this);
                btn.button('loading');

            $('#msg_mb_nick').load(rt_path + '/_trans/register/nick', {
                'reg_mb_nick': $('#reg_mb_nick').val()
            }, function(req) {
                mb_nick_enabled = $('#reg_mb_nick').val();
                $('#reg_mb_nick').valid();
                btn.button('reset');
            });
        });

        $('#btn_email').click(function() {
            var btn = $(this);
                btn.button('loading');

            $('#msg_mb_email').load(rt_path + '/_trans/register/email', {
                'reg_mb_email': $('#reg_mb_email').val()
            }, function(req) {
                mb_email_enabled = $('#reg_mb_email').val();
                $('#reg_mb_email').valid();
                btn.button('reset');
            });
        });
        
        
        $.validator.addMethod("reg_mb_id", function(value, element) {
            return this.optional(element) || (($('#msg_mb_id').text() == '사용하셔도 좋은 아이디 입니다.' && mb_id_enabled == value) || $("input[name='w']").val() == 'u');
        }, "아이디 중복확인 결과가 올바르지 않습니다.");

        $.validator.addMethod("reg_mb_nick", function(value, element) {
            return this.optional(element) || (($('#msg_mb_nick').text() == '사용하셔도 좋은 별명 입니다.' && mb_nick_enabled == value) || ($("input[name='w']").val() == 'u' && $("input[name='mb_nick']").prop('defaultValue') == $("input[name='mb_nick']").val()));
        }, "별명 중복확인 결과가 올바르지 않습니다.");

        $.validator.addMethod("reg_mb_email", function(value, element) {
            return this.optional(element) || (($('#msg_mb_email').text() == '사용하셔도 좋은 이메일 주소입니다.' && mb_email_enabled == value) || ($("input[name='w']").val() == 'u' && $("input[name='mb_email']").prop('defaultValue') == $("input[name='mb_email']").val()));
        }, "이메일 중복확인 결과가 올바르지 않습니다.");
    });
}