if (typeof(KCAPTCHA_JS) == 'undefined') {
    if (typeof rt_path == 'undefined')
        alert('올바르지 않은 접근입니다.');

    var KCAPTCHA_JS = true
      , md5_norobot_key = '';
      
    $(function() {
        $('body').on('click', '#kcaptcha', function() {
            $.post(rt_path + '/_trans/kcaptcha/session', function(data) {
                $('#kcaptcha').attr('src', rt_path + '/_trans/kcaptcha/image/' + (new Date).getTime());
                md5_norobot_key = data;
            });
        });
        $('#kcaptcha').trigger('click');

        if (typeof $.validator != 'undefined') {
            $.validator.addMethod('wrKey', function(value, element) {
                return this.optional(element) || hex_md5(value) == md5_norobot_key;
            });
        }
    });
}