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
                <div v-if="fullyDefinedOffer">
                    <p class="warning">
                        {{ $t("messages.info.not_fully_defined")}}
                    </p>
                </div>
                <div v-else-if="possibleReceiverClinics.length === 0">
                    <p class="warning">
                        {{ $t("messages.danger.no_single_responsible")}}
                    </p>
                </div>
                <div v-else-if="sender.noneSelected && receiver.noneSelected">
                    <p class="warning">
                        {{$t("messages.warning.no_events_selected")}}
                    </p>
                </div>
                <div v-else>
                    <p class="lead">{{ $t("offer.name") }}</p>

                    <div v-if="!sender.noneSelected">
                        <p>{{$t("messages.sender_events")}}</p>
                        <EventGrid v-if="sender.selectedEvents.length > 0"
                                   :events="sender.selectedEvents"
                                   :disabled-events="sender.selectedEvents"/>
                    </div>

                    <div v-if="!receiver.noneSelected">
                        <p>{{$t("messages.receiver_events")}}</p>
                        <EventGrid v-if="receiver.selectedEvents.length > 0"
                                   :events="receiver.selectedEvents"
                                   :disabled-events="receiver.selectedEvents"/>
                    </div>

                    <p>{{$t("messages.receiver")}}</p>
                    <select class="form-control form-control-sm" v-if="possibleReceiverClinics.length > 1"
                            v-model="receiver.selectedClinic">
                        <option v-for="option in possibleReceiverClinics" v-bind:value="option">
                            {{ option.name }}
                        </option>
                    </select>
                    <span v-else class="text-secondary">{{ receiver.selectedClinic.name }}</span>
                    
                    <select class="form-control form-control-sm"
                            v-if="possibleReceiverDoctors.length > 1"
                            v-model="receiver.selectedDoctor">
                        <option v-for="option in possibleReceiverDoctors" v-bind:value="option">
                            {{ option.fullName }}
                        </option>
                    </select>
                    <span v-else class="text-secondary">{{ receiver.selectedDoctor.fullName }}</span>

                    <div class="form-group">
                        <label for="description" class="col-form-label">{{ $t("offer.description")}}</label>
                        <textarea class="form-control" id="description" v-model="description"></textarea>
                    </div>
                    <button class="btn btn-primary">
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
                description: ""
            }
        },
        components: {
            Participant,
            EventSelect,
            AtomSpinner,
            EventGrid
        },
        computed: {
            possibleReceiverClinics: function () {
                let res = this.clinics;
                //check if there is an allowed clinic
                if (this.receiver.selectedEvents.length === 0) {
                    if (this.receiver.selectedClinic === null) {
                        this.receiver.selectedClinic = this.clinics[0];
                    }
                    return res;
                }

                let allowedClinic = this.receiver.selectedEvents[0].clinic;

                //get clinic from clinic list
                let matchingClinics = this.clinics.filter(c => c.id === allowedClinic.id);
                if (matchingClinics.length !== 1) {
                    this.receiver.selectedClinic = null;
                    return [];
                }
                let clinic = matchingClinics[0];

                //check that all events from same clinic
                if (this.receiver.selectedEvents.filter(e => e.clinic.id !== clinic.id).length > 0) {
                    this.receiver.selectedClinic = null;
                    return [];
                }

                this.receiver.selectedClinic = clinic;
                return [clinic];
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