var area;
var simplemde;

function initialize_admin() {
    area = $("textarea.markup")[0];
    if (area != undefined) {
        simplemde = new SimpleMDE(
            {
                element: area,
                spellChecker: false,
                toolbar: ["bold", "italic", "heading-3", "heading-4", "|", "quote", "unordered-list", "ordered-list", "|", "link", "image", "table", "|", "preview", "guide"]
            }
        );

        simplemde.codemirror.on("change", function () {
                area.value = simplemde.value();
                console.log("save data: " + simplemde.value());
            }
        );
    }
}
