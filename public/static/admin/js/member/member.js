define(['jquery', 'utils'], function ($, utils) {
    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        pk: 'member_id',
        index_url: 'member/index',
        add_url: 'member/add',
        view_url: 'member/view',
        modify_url: 'member/modify',
    }

    return {
        index: function () {
            utils.table.render({
                init: init,
                toolbar: ['refresh', 'add'],
                cols: [[
                    {field: 'member_id', maxWidth: 60, search: false, title: '会员ID'},
                    {field: 'nickname', minWidth: 100, title: '昵称'},
                    {field: 'phone', minWidth: 100, title: '手机号'},
                    {field: 'email', minWidth: 100, title: '邮箱'},
                    {field: 'avatar', maxWidth: 60, title: '头像', search: false, templet: utils.table.image},
                    {
                        field: 'status',
                        title: '状态',
                        width: 90,
                        search: 'select',
                        selectList: {0: '禁用', 1: '开启'},
                        templet: utils.table.switch
                    },
                    {field: 'register_time', minWidth: 120, search: 'range', title: '注册时间'},
                    {field: 'register_ip', minWidth: 120, search: false, title: '注册IP'},
                    {field: 'login_ip', minWidth: 120, search: false, title: '登入IP'},
                    {
                        width: 100,
                        title: '操作',
                        templet: utils.table.tool,
                        operat: [
                            [{
                                text: '查看',
                                url: init.view_url,
                                icon: 'fa fa-eye',
                                method: 'open',
                                auth: 'view',
                                field: 'member_id',
                                class: 'layui-btn layui-btn-primary layui-btn-xs',
                            }]
                        ]
                    }
                ]]
            })
            utils.listen()
        },
        add: function () {
            utils.listen()
        },
        view: function () {
            utils.listen()
        }
    }
})