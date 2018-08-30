<template>
    <div id="trade-app">
        <div class="row">
            <div class="col-md-4">
                <p class="lead">{{ $t("actions.choose_sender_events") }}</p>

                <EventSelect :owner="sender"/>
            </div>
            <div class="col-md-4">
                <p class="lead">{{ $t("actions.choose_receiver_events") }}</p>

                <EventSelect :owner="receiver"/>
            </div>
            <div class="col-md-4">
                <p class="lead">{{ $t("offer.name") }}</p>
                <div v-if="fullyDefinedOffer">
                    <p class="warning">
                        {{ $t("messages.info.not_fully_defined")}}
                    </p>
                </div>
                <div v-else-if="possibleSenderClinics.length === 0">
                    <p class="warning">
                        {{ $t("messages.danger.no_single_sender_responsible")}}
                    </p>
                </div>
                <div v-else-if="possibleReceiverClinics.length === 0">
                    <p class="warning">
                        {{ $t("messages.danger.no_single_receiver_responsible")}}
                    </p>
                </div>
                <div v-else-if="sender.noneSelected && receiver.noneSelected">
                    <p class="warning">
                        {{$t("messages.warning.no_events_selected")}}
                    </p>
                </div>
                <div v-else>
                    <div v-if="!sender.noneSelected" class="mb-4">
                        <p>{{$t("messages.sender_events")}}</p>
                        <EventGrid v-if="sender.selectedEvents.length > 0"
                                   :events="sender.selectedEvents"
                                   :disabled-events="sender.selectedEvents"/>
                    </div>
                    <div v-if="possibleSenderClinics.length > 1" class="mb-4">
                        <p>{{$t("actions.choose_sender_clinic")}}</p>
                        <select class="form-control form-control-sm"
                                v-model="sender.selectedClinic">
                            <option v-for="option in possibleSenderClinics" v-bind:value="option">
                                {{ option.name }}
                            </option>
                        </select>
                    </div>

                    <div v-if="!receiver.noneSelected" class="mb-4">
                        <p>{{$t("messages.receiver_events")}}</p>
                        <EventGrid v-if="receiver.selectedEvents.length > 0"
                                   :events="receiver.selectedEvents"
                                   :disabled-events="receiver.selectedEvents"/>
                    </div>

                    <p>{{$t("messages.receiver")}}</p>

                    <select class="form-control form-control-sm"
                            v-if="possibleReceiverDoctors.length > 1"
                            v-model="receiver.selectedDoctor">
                        <option v-for="option in possibleReceiverDoctors" v-bind:value="option">
                            {{ option.fullName }}
                        </option>
                    </select>
                    <span v-else-if="receiver.selectedDoctor !== null">{{ receiver.selectedDoctor.fullName }}</span> <br/>

                    <select class="form-control form-control-sm" v-if="possibleReceiverClinics.length > 1"
                            v-model="receiver.selectedClinic">
                        <option v-for="option in possibleReceiverClinics" v-bind:value="option">
                            {{ option.name }}
                        </option>
                    </select>
                    <span v-else class="text-secondary">{{ receiver.selectedClinic.name }}</span>

                    <div class="form-group">
                        <label for="description" class="col-form-label">{{ $t("offer.description")}}</label>
                        <textarea class="form-control" id="description" v-model="description"></textarea>
                    </div>
                    <button class="btn btn-primary" @click="createOffer">
                        {{ $t("actions.create_offer")}}
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import EventGrid from '../components/EventGrid'
    import EventSelect from "./components/EventSelect"
    import {AtomSpinner} from 'epic-spinners'
    import axios from "axios"
    import Participant from "./components/Participant";

    export default {
        data() {
            return {
                sender: {
                    events: [],
                    selectedEvents: [],
                    noneSelected: false,
                    loading: false,
                    selectedClinic: null,
                    doctor: null
                },
                receiver: {
                    events: [],
                    selectedEvents: [],
                    noneSelected: false,
                    loading: false,
                    selectedClinic: null,
                    selectedDoctor: null
                },
                clinics: [],
                description: "",
                creatingOffer: false
            }
        },
        components: {
            Participant,
            EventSelect,
            AtomSpinner,
            EventGrid
        },
        computed: {
            allSenderClinics: function () {
                if (this.doctor === null || this.clinics === null) {
                    return [];
                }

                //get clinics of doctor
                let doctorClinicIds = this.sender.doctor.clinics.map(c => c.id);
                return this.clinics.filter(c => doctorClinicIds.includes(c.id));
            },
            possibleSenderClinics: function () {
                let clinics = this.sender.noneSelected ? this.allSenderClinics : this.possibleClinics(this.allSenderClinics, this.sender.selectedEvents);
                this.sender.selectedClinic = this.defaultClinic(clinics, this.sender.selectedClinic);
                return clinics;
            },
            possibleReceiverClinics: function () {
                let clinics = this.possibleClinics(this.clinics, this.receiver.selectedEvents);
                this.receiver.selectedClinic = this.defaultClinic(clinics, this.receiver.selectedClinic);
                return clinics;
            },
            possibleReceiverDoctors: function () {
                if (this.receiver.selectedClinic == null) {
                    return [];
                }

                //check for doctor assigned events
                let allowedDoctor = null;
                let invalid = false;
                this.receiver.selectedEvents.forEach(e => {
                        if (e.doctor !== null) {
                            if (allowedDoctor === null) {
                                allowedDoctor = e.doctor;
                            } else if (allowedDoctor.id !== e.doctor.id) {
                                invalid = true;
                            }
                        }
                    }
                );

                if (invalid) {
                    return [];
                }

                if (allowedDoctor != null) {
                    return [allowedDoctor];
                }

                return this.receiver.selectedClinic.doctors;
            },
            fullyDefinedOffer: function () {
                return (this.sender.selectedEvents.length === 0 && !this.sender.noneSelected) || (this.receiver.selectedEvents.length === 0 && !this.receiver.noneSelected);
            }
        },
        methods: {
            createOffer: function () {
                this.creatingOffer = true;
                const senderEvents = this.sender.noneSelected ? [] : this.sender.selectedEvents.map(e => e.id);
                const receiverEvents = this.receiver.noneSelected ? [] : this.receiver.selectedEvents.map(e => e.id);
                axios.post("/api/trade/create", {
                    "sender_event_ids": senderEvents,
                    "receiver_event_ids": receiverEvents,
                    "sender_clinic_id": this.sender.selectedClinic.id,
                    "receiver_clinic_id": this.receiver.selectedClinic.id,
                    "receiver_doctor_id": this.receiver.selectedDoctor.id,
                    "description": this.description
                }).then((response) => {
                    window.location.reload(true);
                });
            },
            possibleClinics: function (clinics, events) {
                let res = clinics;
                //check if there is an allowed clinic
                if (events.length === 0) {
                    return res;
                }

                let allowedClinic = events[0].clinic;

                //get clinic from clinic list
                let matchingClinics = res.filter(c => c.id === allowedClinic.id);
                if (matchingClinics.length !== 1) {
                    return [];
                }
                let clinic = matchingClinics[0];

                //check that all events from same clinic
                if (events.filter(e => e.clinic.id !== clinic.id).length > 0) {
                    return [];
                }

                return [clinic];
            },
            defaultClinic: function (clinics, currentClinic) {
                if (clinics.includes(currentClinic)) {
                    return currentClinic;
                }

                return clinics.length > 0 ? clinics[0] : null;
            }
        },
        watch: {
            possibleReceiverDoctors: function () {
                if (this.possibleReceiverDoctors.length > 0) {
                    if (this.receiver.selectedDoctor === null || !this.possibleReceiverDoctors.includes(this.receiver.selectedDoctor)) {
                        this.receiver.selectedDoctor = this.possibleReceiverDoctors[0];
                    }
                }
            }
        },
        mounted() {
            axios.get("/api/trade/my_events")
                .then((response) => {
                    this.sender.events = response.data;
                    this.sender.loading = false;
                });

            axios.get("/api/trade/their_events")
                .then((response) => {
                    this.receiver.events = response.data;
                    this.receiver.loading = false;
                });

            axios.get("/api/trade/self")
                .then((response) => {
                    this.sender.doctor = response.data;
                });

            axios.get("/api/trade/clinics")
                .then((response) => {
                    this.clinics = response.data;
                });
        },
    }

</script>