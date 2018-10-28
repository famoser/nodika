<template>
    <div>
        <div class="form-group">
            <label for="name" class="col-form-label">{{ $t("generation.name")}}</label>
            <input type="text" class="form-control" id="name" v-model="name"/>
        </div>
        <div class="form-group">
            <label for="start" class="col-form-label">{{ $t("generation.start")}}</label>
            <input type="datetime-local" class="form-control" id="start" v-model="start"/>
        </div>
        <div class="form-group">
            <label for="end" class="col-form-label">{{ $t("generation.end")}}</label>
            <input type="datetime-local" class="form-control" id="end" v-model="end"/>
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
    </div>
</template>


<script>
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
                start: this.generation.startDateTime.slice(0, -6),
                end: this.generation.endDateTime.slice(0, -6),
                mode: this.getModeName(this.generation.startCronExpression, this.generation.endCronExpression),
                modeConfig: {
                    day: {startCronExpression: '* 8 * * *', endCronExpression: '* 8 * * *'}
                }
            }
        },
        methods: {
            save() {
                this.generation.name = this.name;
                this.generation.startDateTime = moment(this.startDateTime).toISOString();
                this.generation.endDateTime = moment(this.endDateTime).toISOString();
                this.generation.startCronExpression = this.startCronExpression;
                this.generation.endCronExpression = this.name;
            },
            getModeName(startCronExpression, endCronExpression) {
                var match = null;
                for (var prop in this.modeConfig) {
                    if (this.modeConfig.hasOwnProperty(prop)) {
                        if (this.modeConfig[prop].startCronExpression === startCronExpression &&
                            this.modeConfig[prop].endCronExpression === endCronExpression) {
                            match = prop;
                        }
                    }
                }
                return match;
            }
        },
        mounted() {
            console.log(this.generation.startDateTime.slice(0, -6));
        }
    }
</script>