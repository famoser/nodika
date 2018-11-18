import 'promise-polyfill/src/polyfill';

// Plain Javascript
//event listener: DOM ready
function addLoadEvent(func) {
    var oldonload = window.onload;
    if (typeof window.onload != 'function') {
        window.onload = func;
    } else {
        window.onload = function () {
            if (oldonload) {
                oldonload();
            }
            func();
        }
    }
}

//call plugin function after DOM ready
addLoadEvent(function () {
    outdatedBrowser({
        bgColor: '#f25648',
        color: '#ffffff',
        cssProp: 'borderImage'
    })
});

var outdatedBrowser = function (options) {
    //Variable definition (before ajax)
    var outdated = document.getElementById("outdated");

    //Define opacity and fadeIn/fadeOut functions
    var done = true;

    function function_opacity(opacity_value) {
        outdated.style.opacity = opacity_value / 100;
        outdated.style.filter = 'alpha(opacity=' + opacity_value + ')';
    }

    function function_fade_in(opacity_value) {
        function_opacity(opacity_value);
        if (opacity_value === 1) {
            outdated.style.display = 'block';
        }
        if (opacity_value === 100) {
            done = true;
        }
    }
    var supports = (function () {
        var div = document.createElement('div'),
            vendors = 'Khtml Ms O Moz Webkit'.split(' '),
            len = vendors.length;

        return function (prop) {
            if (prop in div.style) return true;

            prop = prop.replace(/^[a-z]/, function (val) {
                return val.toUpperCase();
            });

            while (len--) {
                if (vendors[len] + prop in div.style) {
                    return true;
                }
            }
            return false;
        };
    })();

    //if browser does not supports css3 property (transform=default), if does > exit all this
    if (!supports('' + options.cssProp + '')) {
        if (done && outdated.style.opacity !== '1') {
            done = false;
            for (var i = 1; i <= 100; i++) {
                setTimeout((function (x) {
                    return function () {
                        function_fade_in(x);
                    };
                })(i), i * 8);
            }
        }
    } else {
        return;
    }

    startStylesAndEvents();

    //events and colors
    function startStylesAndEvents() {
        var btnClose = document.getElementById("btnCloseUpdateBrowser");
        var btnUpdate = document.getElementById("btnUpdateBrowser");

        //check settings attributes
        outdated.style.backgroundColor = options.bgColor;
        //way too hard to put !important on IE6
        outdated.style.color = options.color;
        outdated.children[0].style.color = options.color;
        outdated.children[1].style.color = options.color;

        //check settings attributes
        btnUpdate.style.color = options.color;
        // btnUpdate.style.borderColor = options.color;
        if (btnUpdate.style.borderColor) btnUpdate.style.borderColor = options.color;
        btnClose.style.color = options.color;

        //close button
        btnClose.onmousedown = function () {
            outdated.style.display = 'none';
            return false;
        };

        //Override the update button color to match the background color
        btnUpdate.onmouseover = function () {
            this.style.color = options.bgColor;
            this.style.backgroundColor = options.color;
        };
        btnUpdate.onmouseout = function () {
            this.style.color = options.color;
            this.style.backgroundColor = options.bgColor;
        };
    }//end styles and events


////////END of outdatedBrowser function
};