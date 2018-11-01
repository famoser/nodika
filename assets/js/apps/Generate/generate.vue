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
                        {{ $t("recipient.title") }}
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
        <div class="col-md-5">
            <div v-if="generation && clinics">
                <Span :generation="generation"
                      v-if="generation.step === 0"
                      @save="savedProperties(arguments[0])" @proceed="proceed"/>
                <Receiver :clinics="clinics" :selected-clinics="selectedClinics"
                          v-if="generation.step === 1"
                          @save="savedClinics(arguments[0])" @proceed="proceed"/>
                <Span :generation="generation" v-if="generation.step === 2"/>
                <Span :generation="generation" v-if="generation.step === 3"/>
            </div>
        </div>
        <div class="col-md-5" v-if="events != null">
            <EventPreview :events="events"/>
        </div>
    </div>
</template>

<script>
    import axios from "axios"
    import Span from "./components/Span";
    import EventPreview from "./components/EventPreview";
    import moment from "moment";
    import Receiver from "./components/Receiver";

    moment.locale('de');

    export default {
        components: {Receiver, Span, EventPreview},
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
            savedClinics(clinics) {
                this.saveClinics();
            },
            proceed() {
                this.generation.step += 1;
                this.saveProps();
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
                console.log(payload["startDateTime"]);
                axios.post(window.location + "/update", payload).then(response => {
                    this.generation = response.data;
                    console.log(this.generation.startDateTime);
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
                }).then(response => {
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
                //parse
                var dateTimes = holidays.select(holiday => {
                    return moment(holiday, "dd.mm.YYYY")
                });

                axios.post(window.location + "/update", {
                    dateExceptions: dateTimes.map(c => {
                        return {
                            eventType: 4,
                            startDateTime: c.toISOString(),
                            endDateTime: c.add(1, 'days').toISOString()
                        }
                    })
                }).then(() => this.reloadEvents());
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