<template>
    <div>
        <div v-if="stateValid && otherStateValid">

            <div class="row">
                <div class="col-md-12">
                <span v-if="possibleUsers.length > 1">
                    <select v-model="selectedUser">
                        <option v-for="option in possibleUsers" v-bind:value="option">
                            {{ option.fullName }}
                        </option>
                    </select>
                </span>
                    <span v-if="possibleUsers.length === 1 && selectedUser">
                    {{ selectedUser.fullName }}
                </span>

                    <span v-if="members.length > 1">
                    <select v-model="selectedMember">
                        <option v-for="option in members" v-bind:value="option">
                            {{ option.name }}
                        </option>
                    </select>
                </span>
                    <span class="text-secondary" v-if="members.length === 1 && selectedMember">
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
        <div v-else-if="!stateValid">
            <p>
                <span class="text-danger">{{$t("invalid_state")}}</span><br/>
                {{$t("invalid_state_explanation")}}
            </p>
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
                membersLoading: false,
                allMembers: [],
                stateValid: true,
                selectedUser: null,
                selectedMember: null,
                possibleUsers: []
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
            events: {
                type: Array,
                required: true
            },
            verifyEvents: {
                type: Array,
                required: true
            },
            otherStateValid: {
                type: Boolean,
                required: true
            }
        },
        mounted() {
            if (this.users.length > 0) {
                this.selectedUser = this.users[0];
                this.refreshMembers();
            } else {
                this.stateValid = false;
            }
        },
        watch: {
            selectedUser: function () {
                this.refreshMembers();
            },
            stateValid: function () {
                this.$emit("state_valid", this.stateValid);
            },
            verifyEvents: function () {
                this.refreshMembers()
            },
            events: function () {
                this.refreshMembers()
            }
        },
        computed: {
            members: function () {
                let res;
                //check if there is an allowed member
                if (this.verifyEvents.length > 0) {
                    let allowedMember = this.verifyEvents[0].member;

                    //check if member is contained in memberlist
                    if (this.allMembers.filter(m => m.id === allowedMember.id).length === 0) {
                        this.stateValid = false;
                        return
                    }

                    //check if all events belong to same member
                    for (let i = 1; i < this.verifyEvents.length; i++) {
                        if (this.verifyEvents[i].member.id !== allowedMember.id) {
                            this.stateValid = false;
                            return
                        }
                    }

                    this.selectedMember = allowedMember;
                    res = [allowedMember];
                } else {
                    this.selectedMember = this.allMembers[0];
                    res = this.allMembers;
                }

                //set possibleUsers
                if (res.length > 0) {
                    this.possibleUsers = this.users.filter(u => res.filter(m => u.members.filter(m2 => m2.id === m.id).length > 0).length > 0);
                } else {
                    this.possibleUsers = [];
                }

                this.stateValid = true;
                return res;
            }
        },
        methods: {
            refreshMembers: function () {
                this.membersLoading = true;
                axios.get("/trade/api/members/" + this.selectedUser.id)
                    .then((response) => {
                        this.allMembers = response.data;

                        this.membersLoading = false;
                        this.stateValid = true;
                    });
            }
        }
    }
</script>