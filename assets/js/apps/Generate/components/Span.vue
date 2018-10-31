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
        <div class="d-flex d-justify-content-right">
            <button class="btn btn-primary" @click="save()">
                {{$t("actions.save_and_continue")}}
            </button>
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
                name: this.generation.name,
                start: new Date(this.generation.startDateTime),
                end: new Date(this.generation.endDateTime),
                mode: null,
                modeConfig: [
                    {name: "day", startCronExpression: '* * *', endCronExpression: '* * *'},
                    {name: "week", startCronExpression: '*/7 * *', endCronExpression: '*/7 * *'}
                ]
            }
        },
        methods: {
            save() {
                let copy = Object.assign({}, this.generation);
                copy.name = this.name;
                copy.startDateTime = new Date(this.start);
                copy.endDateTime = new Date(this.end);

                for (let entry of this.modeConfig) {
                    if (entry.name === this.mode) {
                        let time = copy.startDateTime.getMinutes() + " " + copy.startDateTime.getHours() + " ";
                        copy.startCronExpression = time + entry.startCronExpression;
                        copy.endCronExpression = time + entry.endCronExpression;
                    }
                }

                //datetime to string
                copy.startDateTime = copy.startDateTime.toISOString();
                copy.endDateTime = copy.endDateTime.toISOString();
                this.$emit("saved", copy);
            },
            getModeName(startCronExpression, endCronExpression) {
                for (let entry of this.modeConfig) {
                    if (entry.startCronExpression === startCronExpression.substr(4) &&
                        entry.endCronExpression === endCronExpression.substr(4)) {
                        return entry.name;
                    }
                }
                return null;
            }
        },
        mounted() {
            this.mode = this.getModeName(this.generation.startCronExpression, this.generation.endCronExpression);
        }
    }
</script>