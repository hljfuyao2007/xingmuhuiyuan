class Utils {
    static config = {
        shade: [0.08, '#000'],
        statusName: 'code',
        statusCode: 0,
        prefixUrl: '/api/v1.0'
    }

    constructor() {
    }

    /**
     * post请求
     * @param option
     * @param ok
     * @param no
     * @param ex
     * @returns {boolean}
     */
    post(option, ok, no, ex = undefined) {
        return this.http('post', option, ok, no, ex)
    }

    /**
     * get请求
     * @param option
     * @param ok
     * @param no
     * @param ex
     * @returns {boolean}
     */
    get(option, ok, no, ex = undefined) {
        return this.http('get', option, ok, no, ex)
    }

    /**
     * 接口请求
     * @param type
     * @param option
     * @param ok
     * @param no
     * @param ex
     * @returns {boolean}
     */
    http(type, option, ok, no, ex) {
        let loadIndex = undefined,
            that = this
        type = type || 'get'
        option.url = Utils.config.prefixUrl + option.url || ''
        option.data = option.data || {}
        option.statusName = option.statusName || Utils.config.statusName
        option.statusCode = option.statusCode || Utils.config.statusCode
        option.async = option.async || false
        ok = ok || function (res) {
        }
        no = no || function (res) {
            let msg = res.msg === undefined ? '返回数据格式有误' : res.msg
            that.error(msg);
            return false;
        }
        ex = ex || function (res) {
        }
        if (option.url === '') {
            this.error('请求地址不能为空')
            return false
        }
        $.ajax({
            url: option.url,
            type: type,
            contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            dataType: "json",
            data: option.data,
            async: option.async,
            timeout: 60000,
            beforeSend: (request) => {
                loadIndex = this.loading('加载中')
                // 获取header中的token
                let token = this.getToken()
                if (token) {
                    request.setRequestHeader('token', token)
                }
            },
            success: (res) => {
                let code = eval('res.' + option.statusName)
                if (code === option.statusCode || code === 1) {
                    return ok(res)
                } else {
                    return no(res)
                }
            },
            error: (xhr, textStatus, thrown) => {
                this.error('Status:' + xhr.status + '，' + xhr.statusText + '，请稍后再试！', () => {
                    ex(this);
                })
                return false;
            },
            complete: (xhr) => {
                that.close(loadIndex)
                const token = xhr.getResponseHeader('token')
                if (token) {
                    that.setToken(token)
                }
            }
        })
    }

    /**
     * 设置token
     * @param token
     */
    setToken(token) {
        localStorage.setItem('token', token);
    }

    /**
     * 获取token
     * @returns {string}
     */
    getToken() {
        return localStorage.getItem('token');
    }

    /**
     * 删除token
     */
    rmToken() {
        localStorage.removeItem('token');
    }

    /**
     * 成功消息
     * @param msg
     * @param callback
     * @param options
     * @returns {*}
     */
    success(msg, callback, options) {
        if (callback === undefined) {
            callback = () => {
            }
        }
        return layer.msg(msg, Object.assign({
            icon: 1,
            shade: Utils.config.shade,
            scrollbar: false,
            time: 2000,
            shadeClose: true
        }, options), callback);
    }

    /**
     * 失败消息
     * @param msg
     * @param callback
     * @param options
     * @returns {*}
     */
    error(msg, callback, options) {
        if (callback === undefined) {
            callback = () => {
            }
        }
        return layer.msg(msg, Object.assign({
            icon: 2,
            shade: Utils.config.shade,
            scrollbar: false,
            time: 3000,
            shadeClose: true
        }, options), callback);
    }

    /**
     * 警告消息框
     * @param msg
     * @param callback
     * @returns {*}
     */
    alert(msg, callback) {
        return layer.alert(msg, { end: callback, scrollbar: false });
    }

    /**
     * 对话框
     * @param msg
     * @param ok
     * @param no
     * @returns {*}
     */
    confirm(msg, ok, no) {
        const index = layer.confirm(msg, { title: '操作确认', btn: ['确认', '取消'] }, () => {
            typeof ok === 'function' && ok.call(this);
        }, () => {
            typeof no === 'function' && no.call(this);
            this.close(index);
        });
        return index;
    }

    /**
     * loading
     * @returns {*}
     */
    loading() {
        return layer.load(1, { shade: Utils.config.shade })
    }

    /**
     * 关闭消息框
     * @param index
     * @returns {*}
     */
    close(index) {
        return layer.close(index);
    }

    /**
     * 和PHP一样的时间戳格式化函数
     * @param {string} format 格式
     * @param {int} timestamp 要格式化的时间 默认为当前时间
     * @return {string}   格式化的时间字符串
     */
    date(format, timestamp) {
        var a, jsdate = ((timestamp) ? new Date(timestamp * 1000) : new Date());
        var pad = function (n, c) {
            if ((n = n + "").length < c) {
                return new Array(++c - n.length).join("0") + n;
            } else {
                return n;
            }
        };
        var txt_weekdays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        var txt_weekdays1 = ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"];
        var txt_ordin = {
            1: "st",
            2: "nd",
            3: "rd",
            21: "st",
            22: "nd",
            23: "rd",
            31: "st"
        };
        var txt_months = ["", "January", "February", "March", "April", "May", "June", "July", "August", "September",
            "October", "November", "December"
        ];
        var f = {
            // Day
            d: function () {
                return pad(f.j(), 2)
            },
            D: function () {
                return f.l().substr(0, 3)
            },
            j: function () {
                return jsdate.getDate()
            },
            l: function () {
                return txt_weekdays[f.w()]
            },
            // 中文星期几
            b: function () {
                return txt_weekdays1[f.w()]
            },
            N: function () {
                return f.w() + 1
            },
            S: function () {
                return txt_ordin[f.j()] ? txt_ordin[f.j()] : 'th'
            },
            w: function () {
                return jsdate.getDay()
            },
            z: function () {
                return (jsdate - new Date(jsdate.getFullYear() + "/1/1")) / 864e5 >> 0
            },

            // Week
            W: function () {
                var a = f.z(),
                    b = 364 + f.L() - a;
                var nd2, nd = (new Date(jsdate.getFullYear() + "/1/1").getDay() || 7) - 1;
                if (b <= 2 && ((jsdate.getDay() || 7) - 1) <= 2 - b) {
                    return 1;
                } else {
                    if (a <= 2 && nd >= 4 && a >= (6 - nd)) {
                        nd2 = new Date(jsdate.getFullYear() - 1 + "/12/31");
                        return date("W", Math.round(nd2.getTime() / 1000));
                    } else {
                        return (1 + (nd <= 3 ? ((a + nd) / 7) : (a - (7 - nd)) / 7) >> 0);
                    }
                }
            },

            // Month
            F: function () {
                return txt_months[f.n()]
            },
            m: function () {
                return pad(f.n(), 2)
            },
            M: function () {
                return f.F().substr(0, 3)
            },
            n: function () {
                return jsdate.getMonth() + 1
            },
            t: function () {
                var n;
                if ((n = jsdate.getMonth() + 1) == 2) {
                    return 28 + f.L();
                } else {
                    if (n & 1 && n < 8 || !(n & 1) && n > 7) {
                        return 31;
                    } else {
                        return 30;
                    }
                }
            },

            // Year
            L: function () {
                var y = f.Y();
                return (!(y & 3) && (y % 1e2 || !(y % 4e2))) ? 1 : 0
            },
            //o not supported yet
            Y: function () {
                return jsdate.getFullYear()
            },
            y: function () {
                return (jsdate.getFullYear() + "").slice(2)
            },

            // Time
            a: function () {
                return jsdate.getHours() > 11 ? "pm" : "am"
            },
            A: function () {
                return f.a().toUpperCase()
            },
            B: function () {
                // peter paul koch:
                var off = (jsdate.getTimezoneOffset() + 60) * 60;
                var theSeconds = (jsdate.getHours() * 3600) + (jsdate.getMinutes() * 60) + jsdate.getSeconds() +
                    off;
                var beat = Math.floor(theSeconds / 86.4);
                if (beat > 1000) beat -= 1000;
                if (beat < 0) beat += 1000;
                if ((String(beat)).length == 1) beat = "00" + beat;
                if ((String(beat)).length == 2) beat = "0" + beat;
                return beat;
            },
            g: function () {
                return jsdate.getHours() % 12 || 12
            },
            G: function () {
                return jsdate.getHours()
            },
            h: function () {
                return pad(f.g(), 2)
            },
            H: function () {
                return pad(jsdate.getHours(), 2)
            },
            i: function () {
                return pad(jsdate.getMinutes(), 2)
            },
            s: function () {
                return pad(jsdate.getSeconds(), 2)
            },
            //u not supported yet

            // Timezone
            //e not supported yet
            //I not supported yet
            O: function () {
                var t = pad(Math.abs(jsdate.getTimezoneOffset() / 60 * 100), 4);
                if (jsdate.getTimezoneOffset() > 0) t = "-" + t;
                else t = "+" + t;
                return t;
            },
            P: function () {
                var O = f.O();
                return (O.substr(0, 3) + ":" + O.substr(3, 2))
            },
            //T not supported yet
            //Z not supported yet

            // Full Date/Time
            c: function () {
                return f.Y() + "-" + f.m() + "-" + f.d() + "T" + f.h() + ":" + f.i() + ":" + f.s() + f.P()
            },
            //r not supported yet
            U: function () {
                return Math.round(jsdate.getTime() / 1000)
            }
        };

        var exp = /[\\]?([a-zA-Z])/g,
            ret = '';
        return format.replace(exp, function (t, s) {
            if (t != s) {
                // escaped
                ret = s;
            } else if (f[s]) {
                // a date function exists
                ret = f[s]();
            } else {
                // nothing special
                ret = s;
            }
            return ret;
        });
    }

    /**
     * 时间转时间戳
     * @param date
     * @returns {number}
     */
    strtotime(date) {
        return new Date(date).getTime() / 1000;
    }

    /**
     * 跳转对应链接
     * @param url
     */
    to(url) {
        location.href = url
    }
}