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

                    <span v-if="clinics.length > 1">
                    <select v-model="selectedClinic">
                        <option v-for="option in clinics" v-bind:value="option">
                            {{ option.name }}
                        </option>
                    </select>
                </span>
                    <span class="text-secondary" v-if="clinics.length === 1 && selectedClinic">
                    {{ selectedClinic.name }}
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
                clinicsLoading: false,
                allClinics: [],
                stateValid: true,
                selectedUser: null,
                selectedClinic: null,
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
                this.refreshClinics();
            } else {
                this.stateValid = false;
            }
        },
        watch: {
            selectedUser: function () {
                this.refreshClinics();
            },
            stateValid: function () {
                this.$emit("state_valid", this.stateValid);
            },
            verifyEvents: function () {
                this.refreshClinics()
            },
            events: function () {
                this.refreshClinics()
            }
        },
        computed: {
            clinics: function () {
                let res;
                //check if there is an allowed clinic
                if (this.verifyEvents.length > 0) {
                    let allowedClinic = this.verifyEvents[0].clinic;

                    //check if clinic is contained in cliniclist
                    if (this.allClinics.filter(m => m.id === allowedClinic.id).length === 0) {
                        this.stateValid = false;
                        return
                    }

                    //check if all events belong to same clinic
                    for (let i = 1; i < this.verifyEvents.length; i++) {
                        if (this.verifyEvents[i].clinic.id !== allowedClinic.id) {
                            this.stateValid = false;
                            return
                        }
                    }

                    this.selectedClinic = allowedClinic;
                    res = [allowedClinic];
                } else {
                    this.selectedClinic = this.allClinics[0];
                    res = this.allClinics;
                }

                //set possibleUsers
                if (res.length > 0) {
                    this.possibleUsers = this.users.filter(u => res.filter(m => u.clinics.filter(m2 => m2.id === m.id).length > 0).length > 0);
                } else {
                    this.possibleUsers = [];
                }

                this.stateValid = true;
                return res;
            }
        },
        methods: {
            refreshClinics: function () {
                this.clinicsLoading = true;
                axios.get("/trade/api/clinics/" + this.selectedUser.id)
                    .then((response) => {
                        this.allClinics = response.data;

                        this.clinicsLoading = false;
                        this.stateValid = true;
                    });
            }
        }
    }
</script>