<template>
    <div id="assign-app">
        <div class="row">
            <div class="col-md-4">
                <p class="lead">{{ $t("choose_frontend_user") }}</p>
                <AtomSpinner
                        v-if="usersLoading"
                        :animation-duration="1000"
                        :size="60"
                        :color="'#007bff'"
                />

                <FrontendUserSelectableList
                        v-bind:frontend-users="frontendUsers"
                        @selection-changed="frontendUserSelected">
                </FrontendUserSelectableList>
            </div>
            <div class="col-md-8">
                <div v-if="selectedFrontendUser != null">
                    <div class="d-flex justify-content-between">
                        <p class="lead">{{ $t("assign_events") }}</p>
                        <div>
                            <a href="#" v-on:click.prevent="assignAll" class="btn btn-sm btn-secondary" :class="{'disabled': eventsAssigning}">
                                {{ $t("assign_all_events") }}
                            </a>
                        </div>
                    </div>
                    <AtomSpinner
                            v-if="eventsLoading"
                            :animation-duration="1000"
                            :size="60"
                            :color="'#007bff'"
                    />
                    <EventSelectableList
                            v-bind:events="events"
                            v-bind:selected-frontend-user="selectedFrontendUser"
                            @selected="eventSelected">
                    </EventSelectableList>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import FrontendUserSelectableList from "./components/FrontendUserSelectableList"
    import EventSelectableList from "./components/EventSelectableList"
    import {AtomSpinner} from 'epic-spinners'
    import axios from "axios"

    export default {
        data() {
            return {
                frontendUsers: [],
                selectedFrontendUser: null,
                events: [],
                usersLoading: false,
                eventsLoading: false,
                eventsAssigning: false
            }
        },
        components: {
            FrontendUserSelectableList,
            EventSelectableList,
            AtomSpinner
        },
        methods: {
            frontendUserSelected: function (frontendUser) {
                this.selectedFrontendUser = frontendUser;
                this.events = [];
                this.eventsLoading = true;
                axios.get("/assign/api/assignable_events/" + frontendUser.id)
                    .then((response) => {
                        this.eventsLoading = false;
                        const events = response.data;
                        for (let i = 0; i < events.length; i++) {
                            events[i].isLoading = false;
                        }
                        this.events = events;
                    });
            },
            eventSelected: function (event) {
                if (event.frontendUser != null && event.frontendUser.id === this.selectedFrontendUser.id) {
                    return
                }

                event.isLoading = true;
                axios.get("/assign/api/assign/" + event.id + "/" + this.selectedFrontendUser.id)
                    .then((response) => {
                        event.isLoading = false;
                        event.frontendUser = response.data.frontendUser;
                    });
            },
            assignAll: function () {
                this.events.forEach(e => this.eventSelected(e));
            }
        },
        mounted() {
            this.usersLoading = true;
            axios.get("/assign/api/assignable_users")
                .then((response) => {
                    this.usersLoading = false;
                    const users = response.data;
                    for (let i = 0; i < users.length; i++) {
                        users[i].isSelected = false;
                    }
                    this.frontendUsers = users;
                });
        },
    }

</script>