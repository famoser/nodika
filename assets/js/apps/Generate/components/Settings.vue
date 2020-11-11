<template>
    <div>
        <h4>{{$t("settings.title")}}</h4>

        <p>{{$t("settings.holidays")}}</p>
        <table class="table table-condensed">
            <tbody>
            <tr>
                <td>
                    <input type="text" v-model="newHoliday" class="form-control" @keyup.enter="addHoliday">
                </td>
                <td class="minimal-width">
                    <button class="btn btn-sm btn-outline-primary" @click="addHoliday">
                        <i class="fal fa-plus"></i>
                    </button>
                </td>
            </tr>
            <tr v-for="holiday in holidays">
                <td>{{format(holiday)}}</td>
                <td>
                    <button class="btn btn-sm btn-outline-danger" @click="removeHoliday(holiday)">
                        <i class="fal fa-trash"></i>
                    </button>
                </td>
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
                newHoliday: "",
                holidays: [],
                onlyDateOptions: {format: 'DD.MM.YYYY'},

            }
        },
        methods: {
            removeHoliday: function (holiday) {
                this.holidays.splice(this.holidays.indexOf(holiday), 1);
            },
            addHoliday: function () {
                let newHolidays = this.newHoliday.split(",")
                newHolidays.forEach((holiday) => {
                    let newHoliday = holiday.trim();
                    if (newHoliday.length === 10) {
                      let formatted = moment(newHoliday, "DD.MM.YYYY").toDate();
                      this.holidays.push(formatted);
                    }
                })
                this.orderHolidays();
                this.newHoliday = "";
            },
            format: function (holiday) {
                return moment(holiday).format("DD.MM.YYYY");
            },
            save: function (proceed) {
                this.$emit("save", this.holidays, proceed);
            },
            orderHolidays: function () {
                this.holidays.sort(function (a, b) {
                    return (a > b) - (a < b);
                });
            },
            initialize: function () {
                this.holidays = this.generation.dateExceptions.filter(de => de.eventType === 4).map(de => new Date(de.startDateTime));
                this.orderHolidays();
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
