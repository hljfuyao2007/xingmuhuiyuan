define(['jquery', 'utils'], function ($, utils) {

    var Controller = {
        login: function () {
            if (top.location !== self.location) {
                top.location = self.location;
            }

            $('.bind-password').on('click', function () {
                if ($(this).hasClass('icon-5')) {
                    $(this).removeClass('icon-5');
                    $("input[name='password']").attr('type', 'password');
                } else {
                    $(this).addClass('icon-5');
                    $("input[name='password']").attr('type', 'text');
                }
            });

            $('.icon-nocheck').on('click', function () {
                if ($(this).hasClass('icon-check')) {
                    $(this).removeClass('icon-check');
                } else {
                    $(this).addClass('icon-check');
                }
            });

            $('.login-tip').on('click', function () {
                $('.icon-nocheck').click();
            });

            $('.forget-password').click(function () {
                utils.msg.tips('请联系管理员')
            })

            utils.listen(function (data) {
                data['keep_login'] = $('.icon-nocheck').hasClass('icon-check') ? 1 : 0;
                return data;
            }, function (res) {
                utils.msg.success(res.msg, function () {
                    window.location = utils.url('index/index');
                })
            }, function (res) {
                utils.msg.error(res.msg, function () {
                    $('#refreshCaptcha').trigger("click");
                });
            });
        }
    }

    return Controller
})