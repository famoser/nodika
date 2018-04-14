require("../sass/app.sass");
var $ = require("jquery");
var bootstrap = require("bootstrap");
var multiselect = require("bootstrap-multiselect/dist/js/bootstrap-multiselect.js");

window.$ = $;

FontAwesomeConfig = { autoAddCss: false };

//icons
const fontawesome = require('@fortawesome/fontawesome');
const building = require('@fortawesome/fontawesome-pro-light/faBuilding');
const gavel = require('@fortawesome/fontawesome-pro-light/faGavel');
const user = require('@fortawesome/fontawesome-pro-light/faUser');

fontawesome.library.add(user, building, gavel);


//prevent double submit & give user instant feedback
var disableFormButton = function () {
    var $form = $(this);
    var $buttons = $(".btn", $form);
    if (!$buttons.hasClass("no-disable")) {
        $buttons.addClass("disabled");
    }
};

var initializeSelects = function () {
    $('select[multiple]').multiselect({
        buttonClass: 'btn btn-secondary',
        templates: {
            ul: ' <ul class="multiselect-container dropdown-menu p-1 m-0"></ul>',
            li: '<li><a tabindex="0" class="dropdown-item"><label></label></a></li>'
        },
        buttonContainer: '<div class="dropdown" />',
        nonSelectedText: 'Nichts ausgewählt',
        nSelectedText: 'ausgewählt',
        allSelectedText: 'Alle ausgewählt'
    });
};

var initializeAjax = function (event) {
    event.preventDefault();
    var $form = $(this);
    var url = $form.attr("action");

    $.ajax({
        type: "POST",
        url: url,
        data: $form.serialize(), // serializes the form's elements.
        success: function (data) {
            var $buttons = $(".btn", $form);
            $buttons.removeClass("disabled");
        }
    });
};


$(document).ready(function () {
    $("form").on("submit", disableFormButton);
    initializeSelects();

    //force reload on user browser button navigation
    $(window).on('popstate', function () {
        location.reload(true);
    });

    $('form.ajax-form').on("submit", initializeAjax);
});

