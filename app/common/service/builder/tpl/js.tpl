define(['jquery', 'utils'], function ($, utils) {
    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        pk: '',
        index_url: '',
        add_url: '',
        edit_url: '',
        delete_url: '',
        modify_url: '',
    }

    return {
        index: function() {
            utils.table.render({
                init: init,
                toolbar: ['refresh', 'add'],
                cols: [[]]
            })
            utils.listen()
        },
        add: function() {
            utils.listen()
        },
        edit: function() {
            utils.listen()
        }
    }
})