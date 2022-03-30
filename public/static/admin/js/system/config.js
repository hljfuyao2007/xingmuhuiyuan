define(['jquery', 'utils', 'vue'], function ($, utils, vue) {

    var {form} = layui,
        init = {
            table_elem: '#currentTable',
            table_render_id: 'currentTableRenderId',
            pk: 'file_id',
            index_url: 'config/fileManageList',
            add_url: 'config/fileManageAdd',
            delete_url: 'config/fileManageDel'
        }

    return {
        basic: function () {
            var app = new vue({
                el: '#app',
                data: function () {
                    return {
                        upload_type,
                        sms_type
                    }
                },
                updated() {
                    form.render()
                }
            })

            form.on('radio(upload_type)', function (data) {
                app.upload_type = data.value
            })

            form.on('radio(sms_type)', function (data) {
                app.upload_type = data.value
            })

            utils.common.linkage()
            utils.listen()
        },
        fileManageList: function () {
            utils.table.render({
                init: init,
                toolbar: ['refresh', [{
                    text: '上传',
                    url: init.add_url,
                    auth: 'add',
                    method: 'open',
                    class: 'layui-btn layui-btn-normal layui-btn-sm',
                    icon: 'fa fa-upload',
                }]],
                cols: [[
                    {field: 'url', minWidth: 100, search: false, title: '图片信息', templet: utils.table.image},
                    {field: 'url', minWidth: 120, title: '保存地址', templet: utils.table.url, search: false},
                    {field: 'mime', minWidth: 80, title: 'mime类型'},
                    {field: 'type', minWidth: 80, title: '文件类型'},
                    {field: 'width', minWidth: 80, title: '宽度', search: false},
                    {field: 'height', minWidth: 80, title: '高度', search: false},
                    {field: 'size', minWidth: 80, title: '文件大小(kb)', search: false},
                    {field: 'create_time', minWidth: 80, title: '创建时间', search: 'range'},
                    {width: 80, title: '操作', templet: utils.table.tool, operat: ['delete']}
                ]]
            })
            utils.listen()
        },
        fileManageAdd: function () {
            utils.listen()
        }
    }
})