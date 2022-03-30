define(['jquery', 'utils'], function ($, utils) {
    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        pk: 'id',
        index_url: 'log/index',
        delete_url: 'log/del',
    }

    return {
        index: function () {
            utils.table.render({
                init: init,
                toolbar: ['refresh', 'delete'],
                cols: [[
                    { type: "checkbox" },
                    { field: 'id', width: 80, search: false, title: 'ID' },
                    {
                        field: 'manage.username',
                        fieldAlias: 'system_log.manage_id',
                        minWidth: 100,
                        title: '操作人',
                        selectList: utils.getSelectList('manage', 'username,manage_id')
                    },
                    { field: 'ip', minWidth: 100, title: '操作IP', search: false },
                    { field: 'route', minWidth: 100, title: '操作路由', search: false },
                    { field: 'route_zh', minWidth: 100, title: '操作路由名', search: false },
                    {
                        field: 'content', minWidth: 100, title: '操作内容', search: false,
                        templet: function (data) {
                            return `<a href="javascript: void(0);" onclick="examine(this)" data-content='${data.content}'>
                                        <i class="fa fa-eye"></i>
                                    </a>`
                        }
                    },
                    {
                        field: 'create_time',
                        minWidth: 150,
                        title: '操作时间',
                        search: 'range'
                    },
                    { width: 150, title: '操作', templet: utils.table.tool, operat: ['delete'] }
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