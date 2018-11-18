<template>
    <tr>
        <td v-if="owner">{{owner}}</td>
        <td>{{start}}</td>
        <td>{{end}}</td>
        <td v-if="showEventType">{{eventType}}</td>
    </tr>
</template>


<script>
    import moment from "moment";

    moment.locale('de');

    export default {
        props: {
            event: {
                type: Object,
                required: true
            },
            showEventType: {
                type: Boolean,
                default: false
            }
        },
        computed: {
            start: function () {
                return moment(this.event.startDateTime).format("DD.MM.YYYY HH:mm");
            },
            end: function () {
                return moment(this.event.endDateTime).format("DD.MM.YYYY HH:mm");
            },
            owner: function () {
                var name ="";
                if (this.event.clinic != null) {
                    name = this.event.clinic.name;
                }
                if (this.event.doctor != null) {
                    if (name !== "") {
                        name += " ";
                    }
                    name += this.event.doctor.fullName;
                }
                return name;
            },
            eventType: function () {
                switch (this.event.eventType) {
                    case 1:
                        return this.$t("event_type.weekday");
                    case 2:
                        return this.$t("event_type.saturday");
                    case 3:
                        return this.$t("event_type.sunday");
                    case 4:
                        return this.$t("event_type.holiday");
                }
                return "";
            }
        }
    }
</script>