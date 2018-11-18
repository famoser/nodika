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
        <div class="col-md-5" v-if="generation && clinics && generation.step < 3">
            <Span :generation="generation"
                  v-if="generation.step === 0"
                  @save="savedProperties(...arguments)" />
            <Receiver :clinics="clinics" :selected-clinics="selectedClinics"
                      v-if="generation.step === 1"
                      @save="saveClinics(...arguments)" />
            <Settings :generation="generation"
                      v-if="generation.step === 2"
                      @save="saveHolidays(...arguments)" />
        </div>
        <div class="col-md-5" v-if="generation && generation.step < 3">
            <EventPreview :events="generation.previewEvents"/>
        </div>
        <div class="col-md-10" v-if="generation && generation.step === 3">
            <Preview :generation="generation"
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
                clinics: null
            }
        },
        methods: {
            savedProperties(generation, proceed) {
                this.generation = generation;
                this.saveProps(proceed);
            },
            saveEvents: function () {
                axios.get(window.location + "/apply").then(() => {
                    window.location = "/administration/events";
                })
            },
            saveProps: function (proceed) {
                if (proceed) {
                    this.generation.step = 1;
                }

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
                });
            },
            saveClinics: function (selectedClinics, proceed) {
                if (proceed) {
                    this.generation.step = 2;
                }

                axios.post(window.location + "/update", {
                    step: this.generation.step,
                    targetClinics: selectedClinics.map(c => {
                        return {
                            id: c.id,
                            defaultOrder: 1,
                            weight: c.weight
                        }
                    })
                }).then(response => {
                    this.generation = response.data;
                })
            },
            saveHolidays: function (holidays, proceed) {
                if (proceed) {
                    this.generation.step = 3;
                }

                axios.post(window.location + "/update", {
                    step: this.generation.step,
                    dateExceptions: holidays.map(c => {
                        return {
                            eventType: 4,
                            startDateTime: moment(c).format(),
                            endDateTime: moment(this.addDay(c)).format()
                        }
                    })
                }).then(response => {
                    this.generation = response.data;
                })
            },
            reloadEvents: function() {
                axios.get(window.location + "/update").then((response) => {
                    this.generation = response.data;
                })
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
        },
    }

</script>