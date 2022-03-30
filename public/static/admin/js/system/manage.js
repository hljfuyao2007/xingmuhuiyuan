define(['jquery', 'utils'], function ($, utils) {
    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        pk: 'manage_id',
        index_url: 'manage/index',
        add_url: 'manage/add',
        edit_url: 'manage/edit',
        delete_url: 'manage/del',
        modify_url: 'manage/modify',
    }

    return {
        index: function () {
            utils.table.render({
                init: init,
                toolbar: ['refresh', 'add'],
                cols: [[
                    {field: 'manage_id', width: 60, search: false, title: 'ID'},
                    {field: 'avatar', maxWidth: 60, search: false, title: '头像', templet: utils.table.image},
                    {field: 'username', width: 100, title: '用户名'},
                    {field: 'phone', minWidth: 100, title: '手机号'},
                    {field: 'email', minWidth: 100, title: '邮箱'},
                    {field: 'manageRole.title', minWidth: 100, search: false, title: '所属权限组'},
                    {
                        field: 'status',
                        title: '状态',
                        width: 100,
                        search: 'select',
                        selectList: {0: '禁用', 1: '启用'},
                        templet: utils.table.switch
                    },
                    {field: 'last_login_ip', minWidth: 120, search: false, title: '登入IP'},
                    {field: 'login_num', width: 100, search: false, title: '登入次数'},
                    {field: 'login_time', minWidth: 120, search: false, title: '登入时间'},
                    {field: 'create_time', minWidth: 120, search: false, title: '创建时间'},
                    {width: 150, title: '操作', templet: utils.table.tool}
                ]]
            })
            utils.listen()
        },
        add: function () {
            utils.listen()
        },
        edit: function () {
            utils.listen()
        }
    }
})