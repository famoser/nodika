<template>
    <div id="assign-app">
        <div class="row">
            <div class="col-md-4">
                <FrontendUserSelectableList
                        v-bind:frontend-users="frontendUsers"
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
    import axios from "axios"

    export default {
        data() {
            return {
                frontendUsers: [],
                errors: [],
                loading: false
            }
        },
        components: {
            FrontendUserSelectableList
        },
        methods: {
            selectionChanged: function (frontendUser) {
                console.log("changed selection");
                console.log(frontendUser);
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
    }

</script>