define(['jquery', 'utils'], function ($, utils) {
    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        pk: 'id',
        index_url: 'dian/index',
        edit_url: 'dian/edit',
        add_url: 'dian/add',
        del_url: 'dian/del',
    }

    return {
        index: function () {
            utils.table.render({
                init: init,
                toolbar: ['refresh', 'add'],
                cols: [[
                    { field: 'id', title: 'ID', width: 80, sort: false },
                    { field: 'type_name', title: '类型', minWidth: 100 },
                    { field: 'num', title: '人数', minWidth: 100 },
                    { field: 'money', title: '业绩', minWidth: 100, search: false },
                    { field: 'dian', title: '点位', minWidth: 100, search: false },
                    {
                        width: 150,
                        title: '操作',
                        templet: utils.table.tool,
                        operat: [
                            [{
                                text: '删除',
                                url: init.del_url,
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
        }
    }
})