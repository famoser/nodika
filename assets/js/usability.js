const multiselect = require("bootstrap-multiselect/dist/js/bootstrap-multiselect.js");

//prevent double submit & give user instant feedback
const disableFormButton = function () {
    const $form = $(this);
    const $buttons = $(".btn", $form);
    if (!$buttons.hasClass("no-disable")) {
        $buttons.addClass("disabled");
    }
};

const initializeSelects = function () {
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

const initializeAjax = function (event) {
    event.preventDefault();
    const $form = $(this);
    const url = $form.attr("action");

    $.ajax({
        type: "POST",
        url: url,
        data: $form.serialize(), // serializes the form's elements.
        success: function (data) {
            const $buttons = $(".btn", $form);
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