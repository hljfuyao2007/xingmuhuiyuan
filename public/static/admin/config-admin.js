window.BASE_URL = CONFIG.BASE_URL
require.config({
    urlArgs: "v=" + CONFIG.VERSION,
    baseUrl: BASE_URL + 'admin/plugs/',
    paths: {
        "jquery": ["jquery-3.4.1/jquery-3.4.1.min"],
        // layui
        "layui": ["layui-v2.5.6/layui.all"],
        // layuimini后台扩展
        "miniAdmin": ["lay-module/layuimini/miniAdmin"],
        // layuimini菜单扩展
        "miniMenu": ["lay-module/layuimini/miniMenu"],
        // layuimini 单页扩展
        "miniTab": ["lay-module/layuimini/miniTab"],
        // layuimini 主题扩展
        "miniTheme": ["lay-module/layuimini/miniTheme"],
        // layuimini 统计扩展
        "miniTongji": ["lay-module/layuimini/miniTongji"],
        // 分步表单扩展
        "step": ["lay-module/step-lay/step"],
        // table树形扩展
        "treetable": ["lay-module/treetable-lay/treetable"],
        // table选择扩展
        "tableSelect": ["lay-module/tableSelect/tableSelect"],
        // fa图标选择扩展
        "iconPickerFa": ["lay-module/iconPicker/iconPickerFa"],
        // echarts图表扩展
        "echarts": ["lay-module/echarts/echarts.min"],
        // echarts图表主题扩展
        "echartsTheme": ["lay-module/echarts/echartsTheme"],
        // 省市县区三级联动下拉选择器
        "layarea": ["lay-module/layarea/layarea"],
        // vue
        "vue": ["vue-2.6.10/vue.min"],
        // 工具类
        "utils": ["kernel/utils"],
        // 富文编辑器
        "ckeditor": ["ckeditor4/ckeditor"],
    }
})

// 路径配置信息
var PATH_CONFIG = {
    iconLess: BASE_URL + "/admin/plugs/font-awesome-4.7.0/less/variables.less",
};
window.PATH_CONFIG = PATH_CONFIG;
// 初始化控制器对应的JS自动加载
if ("undefined" != typeof CONFIG.AUTOLOAD_JS && CONFIG.AUTOLOAD_JS) {
    require([BASE_URL + CONFIG.CONTROLLER_JS_PATH], function (Controller) {
        if (eval('Controller.' + CONFIG.ACTION)) {
            eval('Controller.' + CONFIG.ACTION + '()');
        }
    });
}