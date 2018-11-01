<template>
    <div>
        <div class="form-group">
            <label for="name" class="col-form-label">{{ $t("generation.name")}}</label>
            <input type="text" class="form-control" id="name" v-model="name"/>
        </div>
        <div class="form-group">
            <label class="col-form-label">{{ $t("generation.start")}}</label>
            <date-picker v-model="start"></date-picker>
        </div>
        <div class="form-group">
            <label class="col-form-label">{{ $t("generation.end")}}</label>
            <date-picker id="end" v-model="end"></date-picker>
        </div>
        <div class="form-group">
            {{$t("generation.length_of_event")}}
            <div class="custom-control custom-radio">
                <input type="radio" id="customRadio1" name="customRadio" class="custom-control-input"
                       v-model="mode" value="day">
                <label class="custom-control-label" for="customRadio1">{{$t("span.one_day")}}</label>
            </div>
            <div class="custom-control custom-radio">
                <input type="radio" id="customRadio2" name="customRadio" class="custom-control-input"
                       v-model="mode" value="week">
                <label class="custom-control-label" for="customRadio2">{{$t("span.one_week")}}</label>
            </div>
        </div>
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

    moment.locale('de');

    export default {
        props: {
            generation: {
                type: Object,
                required: true
            }
        },
        data() {
            return {
                name: null,
                start: null,
                end: null,
                mode: null,
                modeConfig: [
                    {name: "day", startCronExpression: '* * *', endCronExpression: '* * *'},
                    {name: "week", startCronExpression: '*/7 * *', endCronExpression: '*/7 * *'}
                ]
            }
        },
        methods: {
            save(proceed) {
                let start = moment(this.start);
                let end = moment(this.end);

                let copy = Object.assign({}, this.generation);
                copy.name = this.name;

                for (let entry of this.modeConfig) {
                    if (entry.name === this.mode) {
                        let time = start.format("m H ");
                        copy.startCronExpression = time + entry.startCronExpression;
                        copy.endCronExpression = time + entry.endCronExpression;
                    }
                }

                //datetime to string
                copy.startDateTime = start.format();
                copy.endDateTime = end.format();
                this.$emit("save", copy);
                if (proceed) {
                    this.$emit("proceed", copy);
                }
            },
            shortCron(fullCron) {
                return fullCron.substr(fullCron.indexOf("*"));
            },
            getModeName(startCronExpression, endCronExpression) {
                let startCron = this.shortCron(startCronExpression);
                let endCron = this.shortCron(endCronExpression);
                for (let entry of this.modeConfig) {
                    if (entry.startCronExpression === startCron && entry.endCronExpression === endCron) {
                        return entry.name;
                    }
                }
                return null;
            },
            initialize: function () {
                this.mode = this.getModeName(this.generation.startCronExpression, this.generation.endCronExpression);
                this.name = this.generation.name;
                this.start = new Date(this.generation.startDateTime);
                this.end = new Date(this.generation.endDateTime);
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