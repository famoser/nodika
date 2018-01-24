require("../sass/app.sass");
var $ = require("jquery");

//open navigation menu
var openMenu = function () {
    var menu = $(".menu-content");
    menu.toggleClass("open");
    $(".menu-toggle").toggleClass("open");
    $(".nav-icon").toggleClass("open");

    //close help
    if ($(".help-content").hasClass("open") && menu.hasClass("open")) {
        openHelp();
    }
};

//open help menu
var openHelp = function () {
    $(".help-content").toggleClass("open");
    $(".help-toggle").toggleClass("open");
};

//prevent double submit & give user instant feedback
var disableFormButton = function () {
    var $form = $(this);
    var $buttons = $(".btn", $form);
    if (!$buttons.hasClass("no-disable")) {
        $buttons.addClass("disabled");
    }
};

$(document).ready(function () {
    $("#menu-toggle").on("click", openMenu);
    $("#help-toggle").on("click", openHelp);
    $("form").on("submit", disableFormButton);
});