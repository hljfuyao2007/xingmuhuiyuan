define(['jquery', 'utils', 'treetable', 'iconPickerFa', 'vue'], function ($, utils, Vue) {
    var { table, treetable, iconPickerFa } = layui

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        pk: 'role_id',
        index_url: 'role/index',
        add_url: 'role/add',
        edit_url: 'role/edit',
        delete_url: 'role/del',
        tree_url: 'auth_tree/tree'
    }

    return {
        index: function () {
            utils.table.render({
                init,
                cols: [[
                    { field: 'role_id', width: 60, search: false, title: 'ID' },
                    { field: 'title', minWidth: 100, title: '名称' },
                    { field: 'describe', minWidth: 100, title: '描述' },
                    { field: 'create_time', minWidth: 100, title: '创建时间' },
                    { field: 'update_time', minWidth: 100, title: '更新时间' },
                    {
                        minWidth: 150,
                        title: '操作',
                        templet: utils.table.tool,
                        operat: [
                            [{
                                text: '编辑权限',
                                url: init.tree_url,
                                method: 'open',
                                auth: 'tree',
                                field: init.pk,
                                class: 'layui-btn layui-btn-xs layui-btn-normal',
                                extend: 'data-full="true"'
                            }]
                            , 'edit', 'delete'
                        ]
                    }
                ]],
                done: function () {
                    $('[data-open="auth_tree/tree?role_id=1"]').hide()
                    $('[data-request="role/del?role_id=1"]').hide()
                }
            })
            utils.listen()
        },
        add: function () {
            utils.listen()
        },
        edit: function () {
            utils.listen()
        },
        auth_list: function () {
            var auth_init = {
                table_elem: '#currentTable',
                table_render_id: 'currentTableRenderId',
                pk: 'id',
                index_url: 'role/auth_list',
                add_url: 'role/auth_add',
                edit_url: 'role/auth_edit',
                delete_url: 'role/auth_del',
            }

            var renderTable = function () {
                utils.request.post({ url: utils.url('role/auth_list') }, function (res) {
                    let data = res.data

                    treetable.render({
                        elem: auth_init.table_elem,
                        treeColIndex: 1,
                        homdPid: 99999999,
                        data: data,
                        treeSpid: 0,
                        treeIdName: 'id',
                        treePidName: 'pid',
                        page: false,
                        cellMinWidth: 80,
                        skin: 'line',
                        cols: utils.table.formatCols([[
                            { type: 'numbers', title: '序号' },
                            { field: 'title', title: '权限名称', width: 250, align: 'left' },
                            {
                                field: 'icon', title: '图标', templet: function (d) {
                                    return `<i class="${d.icon}"></i>`
                                }, width: 80, align: 'center'
                            },
                            { field: 'href', title: '菜单url', minWidth: 120 },
                            {
                                field: 'deep', width: 80, title: '类型', templet: function (d) {
                                    switch (d.deep) {
                                        case 1:
                                            return '<span class="layui-badge layui-bg-blue">目录</span>'
                                            break
                                        case 2:
                                        case 3:
                                            return '<span class="layui-badge-rim">菜单</span>'
                                            break
                                        case 4:
                                            return '<span class="layui-badge layui-bg-gray">按钮</span>'
                                            break
                                        default:
                                            return '<span class="layui-badge layui-bg-green">首页</span>'
                                    }
                                }, align: 'center'
                            },
                            {
                                title: '操作',
                                width: 200,
                                align: 'center',
                                templet: utils.table.tool,
                                operat: [
                                    [{
                                        text: '编辑',
                                        url: auth_init.edit_url,
                                        method: 'open',
                                        auth: 'edit',
                                        class: 'layui-btn layui-btn-xs layui-btn-success',
                                        extend: 'data-full="true"'
                                    }],
                                    'delete'
                                ]
                            }
                        ]], auth_init)
                    })
                })
            }

            renderTable()

            $('body').on('click', '[data-treetable-refresh]', function () {
                renderTable();
            });

            $('#btn-add').click(function () {
                utils.open('添加', utils.url('role/auth_add'), '100%', '100%')
            })

            $('#btn-expand').click(function () {
                treetable.expandAll('#currentTable');
            });

            $('#btn-fold').click(function () {
                treetable.foldAll('#currentTable');
            });

            utils.listen()
        },
        auth_add: function () {
            iconPickerFa.render({
                elem: '#iconPicker',
                url: PATH_CONFIG.iconLess,
                search: true,
                page: true,
                limit: 12
            })
            iconPickerFa.checkIcon('iconPicker', '');

            utils.listen(function (data) {
                return data
            }, function (res) {
                utils.msg.success(res.msg, function () {
                    var index = parent.layer.getFrameIndex(window.name);
                    parent.layer.close(index);
                    parent.$('[data-treetable-refresh]').trigger("click");
                })
            })
        },
        auth_edit: function () {
            iconPickerFa.render({
                elem: '#iconPicker',
                url: PATH_CONFIG.iconLess,
                search: true,
                page: true,
                limit: 12
            })
            iconPickerFa.checkIcon('iconPicker', icon);

            utils.listen(function (data) {
                return data
            }, function (res) {
                utils.msg.success(res.msg, function () {
                    var index = parent.layer.getFrameIndex(window.name);
                    parent.layer.close(index);
                    parent.$('[data-treetable-refresh]').trigger("click");
                })
            })
        }
    }
})