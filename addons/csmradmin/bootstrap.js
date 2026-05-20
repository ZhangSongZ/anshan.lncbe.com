if (window.Config.modulename == 'admin' && window.Config.controllername == 'index' && window.Config.actionname == "login") {
    require.config({
        paths: {
            'csmradmin': ['../addons/csmradmin/js/login'],
        },
        shim: {
            csmradmin: ['css!../addons/csmradmin/css/login.css']
        },
    });
    require(["jquery", "csmradmin"], function ($, csmradmin) {
        csmradmin.mounted();
    });
}