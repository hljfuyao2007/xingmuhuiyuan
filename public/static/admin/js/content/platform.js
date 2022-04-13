define(['jquery', 'utils'], function ($, utils) {
    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        pk: 'platform_id',
        index_url: 'platform/index',
        add_url: 'platform/add',
        edit_url: 'platform/edit',
        delete_url: 'platform/del',
        modify_url: 'platform/modify',
    }

    return {
        index: function () {
            utils.table.render({
                init: init,
                cols: [[
                    { field: 'platform_id', maxWidth: 60, search: false, title: 'ID' },
                    { field: 'name', minWidth: 100, title: '平台名' },
                    { field: 'sort', minWidth: 100, title: '排序', sort: false, edit: 'text' },
                    {
                        field: 'is_show',
                        minWidth: 100,
                        title: '显示',
                        selectList: { 0: '隐藏', 1: '显示' },
                        templet: utils.table.switch,
                        tips: '显示|隐藏'
                    },
                    { minWidth: 100, title: '操作', templet: utils.table.tool }
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