<template>
    <div id="assign-app">
        <div class="row">
            <div class="col-md-4">
                <FrontendUserSelectableList
                        @selection-changed="selectionChanged">
                </FrontendUserSelectableList>
            </div>
            <div class="col-md-4">

            </div>
            <div class="col-md-4">

            </div>
        </div>
    </div>
</template>

<script>
    import FrontendUserSelectableList from "../components/FrontendUserSelectableList"

    export default {
        components: {
            FrontendUserSelectableList
        },
        methods: {
            selectionChanged: function (frontendUser) {
                console.log("changed selection");
                console.log(frontendUser);
            }
        }
    }

</script>