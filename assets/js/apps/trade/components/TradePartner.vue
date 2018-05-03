<template>
    <div>
        <div class="row">
            <div class="col-md-12">
                <span v-if="users.length > 1">
                    <select v-model="selectedUser">
                        <option v-for="option in users" v-bind:value="option">
                            {{ option.fullName }}
                        </option>
                    </select>
                </span>
                <span v-if="users.length === 1">
                    {{ selectedUser.fullName }}
                </span>

                <span v-if="members.length > 1">
                    <select v-model="selectedMember">
                        <option v-for="option in members" v-bind:value="option">
                            {{ option.name }}
                        </option>
                    </select>
                </span>
                <span class="text-secondary" v-if="members.length === 1">
                    {{ selectedMember.name }}
                </span>
            </div>
        </div>
        <p>{{$t("receives")}}</p>
        <div v-if="events.length > 0">
            <EventList v-bind:events="events"
                       v-bind:selection-enabled="false">
            </EventList>
        </div>
        <div v-else>
            <p>{{$t("no_events_selected")}}</p>
        </div>
    </div>
</template>


<script>
    import EventList from "./EventList"
    import {AtomSpinner} from 'epic-spinners'
    import axios from "axios"

    export default {
        components: {
            EventList,
            AtomSpinner
        },
        data() {
            return {
                members: [],
                membersLoading: false
            }
        },
        props: {
            users: {
                type: Array,
                required: true
            },
            usersLoading: {
                type: Boolean,
                required: true
            },
            selectedUser: {
                type: Object,
                required: false
            },
            selectedMember: {
                type: Object,
                required: false
            },
            events: {
                type: Array,
                required: true
            }
        },
        mounted() {
            this.refreshMembers();
        },
        watch: {
            selectedUser: function () {
                this.refreshMembers();
            }
        },
        methods: {
            refreshMembers: function () {
                console.log("loaded " + this.selectedUser.id);
                this.membersLoading = true;
                axios.get("/trade/api/members/" + this.selectedUser.id)
                    .then((response) => {
                        const members = response.data;
                        this.members = members;
                        this.selectedMember = members[0];
                        this.membersLoading = false;
                    });
            }
        }
    }
</script>