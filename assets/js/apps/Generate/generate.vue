<template>
    <div class="row">
        <div class="col-md-2">
            <ul class="nav flex-column nav-pills nav-fill" v-if="generation != null">
                <li class="nav-item">
                    <a class="nav-link" :class="{'active': generation.step === 0}"
                       @click.prevent="generation.step = 0" href="#">
                        {{ $t("span.title") }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" :class="{'disabled': generation.step < 1, 'active': generation.step === 1}"
                       @click.prevent="generation.step = 1" href="#">
                        {{ $t("receiver.title") }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" :class="{'disabled': generation.step < 2, 'active': generation.step === 2}"
                       @click.prevent="generation.step = 2" href="#">
                        {{ $t("settings.title") }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" :class="{'disabled': generation.step < 3, 'active': generation.step === 3}"
                       @click.prevent="generation.step = 3" href="#">
                        {{ $t("preview.title") }}
                    </a>
                </li>
            </ul>
        </div>
        <div class="col-md-5" v-if="generation && clinics && events && generation.step < 3">
            <Span :generation="generation"
                  v-if="generation.step === 0"
                  @save="savedProperties(arguments[0])" @proceed="proceed"/>
            <Receiver :clinics="clinics" :selected-clinics="selectedClinics"
                      v-if="generation.step === 1"
                      @save="saveClinics(arguments[0])" @proceed="proceed"/>
            <Settings :generation="generation"
                      v-if="generation.step === 2"
                      @save="saveHolidays(arguments[0])" @proceed="proceed"/>
        </div>
        <div class="col-md-5" v-if="events != null && generation && generation.step < 3">
            <EventPreview :events="events"/>
        </div>
        <div class="col-md-10" v-if="generation && generation.step === 3 && events">
            <Preview :generation="generation" :events="events"
                     @submit="saveEvents" @reload="reloadEvents"/>
        </div>
    </div>
</template>

<script>
    import axios from "axios"
    import Span from "./components/Span";
    import EventPreview from "./components/EventPreview";
    import moment from "moment";
    import Receiver from "./components/Receiver";
    import Settings from "./components/Settings";
    import Preview from "./components/Preview";

    moment.locale('de');

    export default {
        components: {Preview, Settings, Receiver, Span, EventPreview},
        data() {
            return {
                generation: null,
                events: null,
                clinics: null
            }
        },
        methods: {
            savedProperties(generation) {
                this.generation = generation;
                this.saveProps();

            },
            proceed() {
                this.generation.step += 1;
                this.saveProps();
            },
            saveEvents: function () {
                axios.post(window.location + "/apply", {
                    events: this.events.map(e => {
                        return {
                            startDateTime: e.startDateTime,
                            endDateTime: e.endDateTime,
                            clinicId: e.clinic ? e.clinic.id : null,
                            doctorId: e.doctor ? e.doctor.id : null,
                            eventType: e.eventType
                        }
                    })
                }).then(() => {
                    window.location = "/administration/events";
                })
            },
            saveProps: function () {
                var payload = {};
                var props = [
                    'name', 'startCronExpression', 'endCronExpression',
                    'startDateTime', 'endDateTime',
                    'differentiateByEventType', 'mindPreviousEvents',
                    'weekdayWeight', 'saturdayWeight', 'sundayWeight', 'holydayWeight',
                    'step'];
                props.forEach(p => {
                    payload[p] = this.generation[p]
                });
                axios.post(window.location + "/update", payload).then(response => {
                    this.generation = response.data;
                    this.reloadEvents();
                });
            },
            saveClinics: function (selectedClinics) {
                axios.post(window.location + "/update", {
                    targetClinics: selectedClinics.map(c => {
                        return {
                            id: c.id,
                            defaultOrder: 1,
                            weight: c.weight
                        }
                    })
                }).then(() => {
                    this.reloadEvents();
                })
            },
            reloadEvents: function () {
                axios.get(window.location + "/events").then(response => {
                    let counter = 0;
                    response.data.forEach(e => {
                        if (e.id == null) {
                            e.id = counter++;
                        }
                    });
                    this.events = response.data;
                });
            },
            saveHolidays: function (holidays) {
                axios.post(window.location + "/update", {
                    dateExceptions: holidays.map(c => {
                        return {
                            eventType: 4,
                            startDateTime: moment(c).format(),
                            endDateTime: moment(this.addDay(c)).format()
                        }
                    })
                }).then(() => this.reloadEvents());
            },
            addDay: function (input) {
                var result = new Date(input.valueOf());
                result.setDate(result.getDate() + 1);
                return result;
            }
        },
        computed: {
            selectedClinics: function () {
                return this.generation.clinics.map(clinic => {
                    return {
                        id: clinic.clinic.id,
                        weight: clinic.weight,
                    }
                });
            }
        },
        mounted() {
            axios.get(window.location + "/get")
                .then((response) => {
                    this.generation = response.data;
                });
            axios.get(window.location + "/targets")
                .then((response) => {
                    this.clinics = response.data.clinics;
                });
            this.reloadEvents();
        },
    }

</script>