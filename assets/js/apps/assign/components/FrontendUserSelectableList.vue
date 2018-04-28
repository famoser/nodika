<template>
    <div>
        <FrontendUserSelectableListItem
                v-for="frontendUser in frontendUsers"
                v-bind:key="frontendUser.id"
                v-bind:frontend-user="frontendUser"
                @select="selectFrontendUser(frontendUser)"
        >
        </FrontendUserSelectableListItem>
    </div>
</template>


<script>
    import FrontendUserSelectableListItem from "./FrontendUserSelectableListItem"

    export default {
        components: {
            FrontendUserSelectableListItem
        },
        props: {
            frontendUsers: {
                type: Array,
                required: true
            }
        },
        methods: {
            selectFrontendUser: function (frontendUser) {
                this.frontendUsers.forEach(function (f) {
                    f.isSelected = false;
                });
                frontendUser.isSelected = true;
                this.$emit("selection-changed", frontendUser);
            }
        }
    }
</script>