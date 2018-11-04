<template>
    <div>
        <h4>{{$t("receiver.title")}}</h4>
        <table class="table table-hover">
            <thead>
            <tr>
                <th>{{$t('generation.receives_events')}}</th>
                <th>{{$t('clinic.name')}}</th>
                <th>{{$t('generation.weight')}}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="clinicContainer in clinicContainers" :class="{'table-warning': !clinicContainer.selected}">
                <td><input title="include" type="checkbox" v-model="clinicContainer.selected"/></td>
                <td>{{clinicContainer.clinic.name}}</td>
                <td><input title="weight" type="number" v-model="clinicContainer.weight"/></td>
            </tr>
            </tbody>
        </table>
        <hr/>
        <div class="d-flex justify-content-between">
            <div>
                <button class="btn btn-outline-primary" @click="save(false)">
                    {{$t("actions.save")}}
                </button>
            </div>
            <div>
                <button class="btn btn-primary" @click="save(true)">
                    {{$t("actions.save_and_continue")}}
                </button>
            </div>
        </div>
    </div>
</template>


<script>
    import moment from "moment";
    import EventRow from './EventRow'

    moment.locale('de');

    export default {
        props: {
            clinics: {
                type: Array,
                required: true
            },
            selectedClinics: {
                type: Array,
                required: true
            }
        },
        components: {
            EventRow
        },
        data() {
            return {
                clinicContainers: []
            }
        },
        computed: {
            eventsPreview: function () {
                if (this.tooManyEventsCount > 0) {
                    return this.events.filter((_, index) => index < this.previewEventsCount);
                }
                return this.events;
            },
            tooManyEventsCount: function () {
                return this.events.length - 2 * this.previewEventsCount;
            },
            lastEventsPreview: function () {
                let minIndex = this.events.length - this.previewEventsCount;
                return this.events.filter((_, index) => index >= minIndex);
            },
            ownerAssigned: function () {
                return this.events.lenght > 0 && (this.events[0].clinic || this.events[0].doctor);
            }
        },
        methods: {
            save: function (proceed) {
                this.$emit("save", this.clinicContainers.filter(c => c.selected).map(c => {
                    return {id: c.clinic.id, weight: c.weight}
                }));
                if (proceed) {
                    this.$emit("proceed");
                }
            },
            initialize: function () {
                this.clinicContainers = [];
                this.clinics.forEach(c => {
                    let match = this.selectedClinics.filter(sc => sc.id === c.id)[0];
                    this.clinicContainers.push({
                        selected: !!match,
                        weight: match ? match.weight : 1,
                        clinic: c
                    });
                });

                this.clinicContainers.sort(function(cc1, cc2) {
                    return cc1.clinic.name.localeCompare(cc2.clinic.name);
                });
            }
        },
        watch: {
            clinics: function () {
                this.initialize();
            }
        },
        mounted() {
            this.initialize();
        }
    }
</script>