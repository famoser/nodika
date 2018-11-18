<template>
    <div id="assign-app">
        <div class="row">
            <div class="col-md-4">
                <p class="alert alert-info">{{ $t("actions.choose_doctor") }}</p>
                <AtomSpinner
                        v-if="doctorsLoading"
                        :animation-duration="1000"
                        :size="60"
                        :color="'#007bff'"
                />

                <DoctorList
                        :doctors="doctors"
                        :selected-doctor="selectedDoctor"
                        @doctor-selected="doctorSelected">
                </DoctorList>
            </div>
            <div class="col-md-8">
                <div v-if="selectedDoctor != null">
                    <p class="alert alert-info">{{ $t("actions.select_event") }}
                        <a href="#" v-on:click.prevent="assignAll" :class="{'disabled': loadingEvents.length > 0 || eventsLoading}">
                            {{ $t("actions.assign_all_events") }}
                        </a>
                    </p>
                    <AtomSpinner
                            v-if="eventsLoading"
                            :animation-duration="1000"
                            :size="60"
                            :color="'#007bff'"
                    />
                    <EventGrid
                            v-if="!eventsLoading"
                            :events="events"
                            :size="6"
                            :selected-events="doctorEvents"
                            :loading-events="loadingEvents"
                            :disabled-events="doctorEvents"
                            @event-selected="eventSelected">
                        <template slot="placeholder">
                            <p>{{ $t("messages.no_events_for_doctor")}}</p>
                        </template>
                    </EventGrid>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import DoctorList from "./components/DoctorList"
    import EventGrid from "../components/EventGrid"
    import {AtomSpinner} from 'epic-spinners'
    import axios from "axios"

    export default {
        data() {
            return {
                doctorsLoading: true,
                doctors: [],
                selectedDoctor: null,

                eventsLoading: false,
                events: [],
                loadingEvents: [],
            }
        },
        components: {
            DoctorList,
            EventGrid ,
            AtomSpinner
        },
        methods: {
            doctorSelected: function (doctor) {
                if (this.selectedDoctor !== doctor) {
                    this.selectedDoctor = doctor;
                    this.events = [];
                    this.eventsLoading = true;
                    axios.get("/api/assign/events/" + doctor.id)
                        .then((response) => {
                            this.eventsLoading = false;
                            this.events = response.data;
                        });
                }
            },
            eventSelected: function (event) {
                if (event.doctor != null && event.doctor.id === this.selectedDoctor.id) {
                    return
                }

                this.loadingEvents.push(event);
                axios.get("/api/assign/assign/" + event.id + "/" + this.selectedDoctor.id)
                    .then((response) => {
                        this.loadingEvents.splice(this.loadingEvents.indexOf(event), 1);
                        event.doctor = response.data.doctor;
                    });
            },
            assignAll: function () {
                this.events.forEach(e => this.eventSelected(e));
            }
        },
        computed: {
            doctorEvents: function () {
                if (this.selectedDoctor != null) {
                    return this.events.filter(e => e.doctor != null && e.doctor.id === this.selectedDoctor.id);
                }
                return [];
            }
        },
        mounted() {
            axios.get("/api/assign/doctors")
                .then((response) => {
                    this.doctorsLoading = false;
                    this.doctors = response.data;
                    if (this.doctors.length > 0) {
                        this.doctorSelected(this.doctors[0]);
                    }
                });
        },
    }

</script>