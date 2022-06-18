define(['jquery', 'utils'], function ($, utils) {
    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        pk: 'member_id',
        index_url: 'member/index',
        add_url: 'member/add',
        view_url: 'member/view',
        modify_url: 'member/modify',
        check_url: 'member/check',
        open_url: 'member/open',
        team_url: 'member/team',
        stati_url: 'member/statistics',
        money_url: 'member/money',
    }

    return {
        index: function () {
            utils.table.render({
                init: init,
                toolbar: ['refresh', 'add'],
                cols: [[
                    { field: 'member_id', maxWidth: 40, search: false, title: '会员ID' },
                    { field: 'nickname', minWidth: 60, title: '昵称' },
                    { field: 'sex', maxWidth: 30, title: '性别', selectList: { 0: '女', 1: '男' } },
                    { field: 'phone', minWidth: 100, title: '手机号' },
                    //{ field: 'invite_code', minWidth: 100, title: '邀请码' },
                    { field: 'parent_name', minWidth: 100, title: '邀请人' },
                    { field: 'platform', minWidth: 100, title: '平台', selectList: window.platform_arr },
                    { field: 'platform_uid', minWidth: 100, title: '平台ID' },
                    // { field: 'avatar', minWidth: 60, title: '头像', search: false, templet: utils.table.image },
                    //{ field: 'is_agency', minWidth: 100, title: '代理', selectList: { 0: '否', 1: '是' } },
                    {
                        field: 'is_agency',
                        title: '代理',
                        width: 100,
                        search: 'select',
                        selectList: { 0: '关闭', 1: '开启' },
                        templet: utils.table.switch
                    },
                    {
                        field: 'manage',
                        title: '管理员',
                        width: 100,
                        // search: 'select',
                        // selectList: { 0: '禁用', 1: '开启' },
                        //templet: utils.table.switch
                    },
                    {
                        field: 'is_identity',
                        minWidth: 60,
                        title: '认证状态',
                        selectList: { 0: '未认证', 1: '待审核', 2: '通过', 3: '拒绝' }
                    },
                    {
                        field: 'status',
                        title: '状态',
                        width: 100,
                        search: 'select',
                        selectList: { 0: '禁用', 1: '开启' },
                        templet: utils.table.switch
                    },

                    { field: 'register_time', minWidth: 120, search: 'range', title: '注册时间' },
                    {
                        minWidth: 200,
                        title: '操作',
                        templet: utils.table.tool,
                        operat: [
                            [{
                                text: '查看',
                                url: init.view_url,
                                method: 'open',
                                auth: 'view',
                                field: 'member_id',
                                class: 'layui-btn layui-btn-success layui-btn-xs',
                            },{
                                text: '账户',
                                url: init.money_url,
                                method: 'open',
                                auth: 'view',
                                field: 'member_id',
                                class: 'layui-btn layui-btn-normal layui-btn-xs',
                            },{
                                text: '平台',
                                url: init.open_url,
                                method: 'open',
                                auth: 'view',
                                field: 'member_id',
                                class: 'layui-btn layui-btn-normal layui-btn-xs',
                            },{
                                text: '团队',
                                url: init.team_url,
                                method: 'open',
                                auth: 'view',
                                field: 'member_id',
                                class: 'layui-btn layui-btn-normal layui-btn-xs',
                            },{
                                text: '统计',
                                url: init.stati_url,
                                method: 'open',
                                auth: 'view',
                                field: 'member_id',
                                class: 'layui-btn layui-btn-normal layui-btn-xs',
                            }, {
                                text: '审核',
                                url: init.check_url,
                                method: 'open',
                                auth: 'check',
                                field: 'member_id',
                                class: 'layui-btn layui-btn-error layui-btn-xs',
                            }]
                        ]
                    }
                ]],
                done: function (data) {
                    $.each(data.data, function (index, item) {
                        if (item.is_identity === 0) {
                            $(`[data-open='member/check?member_id=${item.member_id}']`).remove();
                        }
                    });
                }
            })
            utils.listen()
        },
        add: function () {
            utils.listen()
        },
        view: function () {
            utils.listen()
        },
        check: function () {
            const reason = $('.reason'),
                status = +$('[name="is_identity"]:checked').val()

            $(function () {
                status === 3 && reason.show()
            })

            layui.form.on('radio(is_identity)', function (data) {
                data = +data.value
                data === 3 ? reason.show() : reason.hide()
            })
            utils.listen()
        }
    }
})