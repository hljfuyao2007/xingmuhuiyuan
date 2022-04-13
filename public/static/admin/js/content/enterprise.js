define(['jquery', 'utils'], function ($, utils) {
    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        pk: 'data_id',
        index_url: 'enterprise/index',
        inc_url: 'enterprise/inc',
    }

    return {
        index: function () {
            utils.table.render({
                init: init,
                toolbar: ['refresh', [{
                    text: '导入',
                    url: init.inc_url,
                    icon: 'fa fa-upload',
                    auth: 'inc',
                    class: 'layui-btn layui-btn-normal layui-btn-sm',
                }]],
                cols: [[
                    { field: 'data_id', maxWidth: 60, search: false, title: 'ID' },
                    { field: 'nickname', minWidth: 100, title: '会员昵称' },
                    { field: 'uid', minWidth: 100, title: '平台ID' },
                    { field: 'date', minWidth: 100, title: '日期', search: 'time', timeType: 'date' },
                    { field: 'enterprise', minWidth: 100, title: '业绩', search: false },
                ]]
            })
            utils.listen()
        },
        inc: function () {
            let loading = 0

            layui.upload.render({
                elem: '#inc',
                url: utils.url(init.inc_url),
                accept: 'file',
                field: 'excel',
                exts: 'xls|xlsx|csv',
                before: function () {
                    $('#inc').attr('disabled', true).addClass('layui-btn-disabled')
                    loading = utils.msg.loading('导入中...')
                },
                done: function (res) {
                    utils.msg.close(loading)
                    if (res.code === 1) {
                        utils.msg.success(res.msg, function () {
                            parent.location.reload()
                        }, { time: 1000 })
                        return
                    }
                    utils.msg.error(res.msg)
                }
            })
            utils.listen()
        }
    }
})