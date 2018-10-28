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
            <div v-if="generation">
                <Span :generation="generation" v-if="generation.step === 0"/>
                <Span :generation="generation" v-if="generation.step === 1"/>
                <Span :generation="generation" v-if="generation.step === 2"/>
                <Span :generation="generation" v-if="generation.step === 3"/>
            </div>

        </div>
    </div>
</template>

<script>
    import axios from "axios"
    import Span from "./components/Span";
    import moment from "moment";

    moment.locale('de');

    export default {
        components: {Span},
        data() {
            return {
                generation: null
            }
        },
        methods: {
            saveProps: function () {
                var payload = {};
                var props = [
                    'name', 'startCronExpression', 'endCronExpression',
                    'startDateTime', 'endDateTime',
                    'differentiateByEventType', 'mindPreviousEvents',
                    'weekdayWeight', 'saturdayWeight', 'sundayWeight', 'holydayWeight',
                    'step'];
                props.forEach(p => {
                    payload[p] = tis.generation[p]
                });
                axios.post(window.location + "/update", payload);
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
                });
            }
        },
        mounted() {
            axios.get(window.location + "/get")
                .then((response) => {
                    console.log(response.data);
                    this.generation = response.data;
                });
        },
    }

</script>