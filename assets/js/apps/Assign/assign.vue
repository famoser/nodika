<template>
    <div id="assign-app">
        <div class="row">
            <div class="col-md-4">
                <p class="lead">{{ $t("actions.choose_doctor") }}</p>
                <AtomSpinner
                        v-if="usersLoading"
                        :animation-duration="1000"
                        :size="60"
                        :color="'#007bff'"
                />

                <DoctorList
                        v-bind:doctors="doctors"
                        @selection-changed="doctorSelected">
                </DoctorList>
            </div>
            <div class="col-md-8">
                <div v-if="selectedDoctor != null">
                    <div class="d-flex justify-content-between">
                        <p class="lead">{{ $t("actions.assign_events") }}</p>
                        <div>
                            <a href="#" v-on:click.prevent="assignAll" class="btn btn-sm btn-secondary" :class="{'disabled': eventsAssigning}">
                                {{ $t("actions.assign_all_events") }}
                            </a>
                        </div>
                    </div>
                    <AtomSpinner
                            v-if="eventsLoading"
                            :animation-duration="1000"
                            :size="60"
                            :color="'#007bff'"
                    />
                    <EventList
                            v-if="!eventsLoading"
                            v-bind:events="events"
                            v-bind:selected-doctor="selectedDoctor"
                            @selected="eventSelected">
                    </EventList>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import DoctorList from "./components/DoctorList"
    import EventList from "./components/EventList"
    import {AtomSpinner} from 'epic-spinners'
    import axios from "axios"

    export default {
        data() {
            return {
                doctors: [],
                selectedDoctor: null,
                events: [],
                usersLoading: false,
                eventsLoading: false,
                eventsAssigning: false
            }
        },
        components: {
            DoctorList,
            EventList,
            AtomSpinner
        },
        methods: {
            doctorSelected: function (doctor) {
                this.selectedDoctor = doctor;
                this.events = [];
                this.eventsLoading = true;
                axios.get("/api/assign/events/" + doctor.id)
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
                if (event.doctor != null && event.doctor.id === this.selectedDoctor.id) {
                    return
                }

                event.isLoading = true;
                axios.get("/api/assign/assign/" + event.id + "/" + this.selectedDoctor.id)
                    .then((response) => {
                        event.isLoading = false;
                        event.doctor = response.data.doctor;
                    });
            },
            assignAll: function () {
                this.events.forEach(e => this.eventSelected(e));
            }
        },
        mounted() {
            this.usersLoading = true;
            axios.get("/api/assign/doctors")
                .then((response) => {
                    this.usersLoading = false;
                    const users = response.data;
                    for (let i = 0; i < users.length; i++) {
                        users[i].isSelected = false;
                    }
                    this.doctors = users;
                });
        },
    }

</script>