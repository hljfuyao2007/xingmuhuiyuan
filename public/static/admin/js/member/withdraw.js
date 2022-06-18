define(['jquery', 'utils'], function ($, utils) {
    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        pk: 'withdraw_id',
        index_url: 'withdraw/index',
        edit_url: 'withdraw/edit',
        edit_all_url: 'withdraw/edit_all',
        export_url: 'withdraw/export',
    }

    return {
        index: function () {
            utils.table.render({
                init: init,
                toolbar: ['refresh','export',
                            [{
                                text: '审核',
                                url: init.edit_all_url,
                                method: 'open',
                                auth: 'edit_all',
                                field: init.pk,
                                class: 'layui-btn layui-btn-normal layui-btn-xs',
                                checkbox:true
                            }]
                        ],
                cols: [[
                    { type: 'checkbox' },
                    { field: 'withdraw_id', title: 'ID', width: 80, sort: false },
                    { field: 'name', title: '姓名', minWidth: 100 },
                    { field: 'account', title: '提现账户', minWidth: 100 },
                    { field: 'money', title: '提现金额', minWidth: 100, search: false },
                    { field: 'platform', title: '平台', minWidth: 100, search: false },
                    { field: 'income_money', title: '实际到账金额', minWidth: 100, search: false },
                    { field: 'rate', title: '手续费率', minWidth: 100, search: false },
                    { field: 'create_time', title: '提现时间', minWidth: 120,search:"range" },
                    { field: 'status', title: '状态', minWidth: 100, selectList: { '0': '待审核', '1': '已打款', '2': '已拒绝' } },
                    {
                        width: 100,
                        title: '操作',
                        templet: utils.table.tool,
                        operat: [
                            [{
                                text: '审核',
                                url: init.edit_url,
                                method: 'open',
                                auth: 'edit',
                                field: init.pk,
                                class: 'layui-btn layui-btn-success layui-btn-xs',
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
        edit: function () {
            utils.listen()
        },
        edit_all: function () {
            utils.listen()
        }
    }
})