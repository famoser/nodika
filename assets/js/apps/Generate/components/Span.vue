<template>
    <div>
        <h4>{{$t("span.title")}}</h4>
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
            <div class="custom-control custom-radio">
                <input type="radio" id="customRadio3" name="customRadio" class="custom-control-input"
                       v-model="mode" value="other">
                <label class="custom-control-label" for="customRadio3">{{$t("span.other")}}</label>
            </div>
        </div>
        <div class="row" v-if="mode === 'other'">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-form-label">{{ $t("generation.startCronExpression")}}</label>
                    <input class="form-control" type="text" v-model="startCronExpression">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-form-label">{{ $t("generation.endCronExpression")}}</label>
                    <input class="form-control" type="text" v-model="endCronExpression">
                </div>
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
                startCronExpression: null,
                endCronExpression: null,
                modes: ["day", "week", "other"]
            }
        },
        methods: {
            calculateCronExpression(generation, mode) {
                if (mode === "day" || mode === "week") {
                    let start = moment(generation.startDateTime);
                    let time = start.format("m H ");
                    if (mode === "day") {
                        return {
                            startCronExpression: time + "* * *",
                            endCronExpression: time + "* * *"
                        }
                    } else {
                        return {
                            startCronExpression: time + "* * " + start.day(),
                            endCronExpression: time + "* * " + start.day()
                        }
                    }
                } else {
                    return {
                        startCronExpression: generation.startCronExpression,
                        endCronExpression: generation.endCronExpression
                    }
                }
            },
            save(proceed) {
                //write properties
                let copy = Object.assign({}, this.generation, this.calculateCronExpression(this.mode));
                copy.name = this.name;

                //datetime to string
                let start = moment(this.start);
                let end = moment(this.end);
                copy.startDateTime = start.format();
                copy.endDateTime = end.format();
                this.$emit("save", copy, proceed);
            },
            getModeName(generation) {
                for (let mode of this.modes) {
                    const expected = this.calculateCronExpression(generation, mode);
                    if (expected.startCronExpression === generation.startCronExpression && expected.endCronExpression === generation.endCronExpression) {
                        return mode;
                    }
                }
                return "other";
            },
            initialize: function () {
                this.mode = this.getModeName(this.generation);
                this.name = this.generation.name;
                this.start = new Date(this.generation.startDateTime);
                this.end = new Date(this.generation.endDateTime);
                this.startCronExpression = this.generation.startCronExpression;
                this.endCronExpression = this.generation.endCronExpression;
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