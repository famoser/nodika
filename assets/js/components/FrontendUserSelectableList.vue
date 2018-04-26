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
    import axios from "axios";
    import FrontendUserSelectableListItem from "./FrontendUserSelectableListItem"

    export default {
        components: {
            FrontendUserSelectableListItem
        },
        data() {
            return {
                frontendUsers: [],
                errors: [],
                loading: false
            }
        },
        mounted() {
            this.loading = true;
            console.log("started");
            axios.get("/assign/api/assignable_users")
                .then((response) => {
                    this.loading = false;
                    const users = response.data;
                    for (let i = 0; i < users.length; i++) {
                        users[i].isSelected = false;
                    }
                    this.frontendUsers = users;
                }, (error) => {
                    this.loading = false;
                })
        },
        methods: {
            selectFrontendUser: function (frontendUser) {
                this.frontendUsers.forEach(function (f) {
                    f.isSelected = false;
                });
                frontendUser.isSelected = true;
                this.$emit("selection-changed", frontendUser);
            }
        },
        computed: {
            isAnySelected: function () {
                let any = false;
                for (const user in this.frontendUsers) {
                    any |= user.isSelected
                }
                return any;
            }
        },
    }
</script>