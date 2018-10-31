<template>
    <tr>
        <td v-if="owner">{{owner}}</td>
        <td>{{start}}</td>
        <td>{{end}}</td>
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
            }
        },
        computed: {
            start: function () {
                return moment(this.event.startDateTime).format("DD.MM.YYYY HH:mm");
            },
            end: function () {
                return moment(this.event.startDateTime).format("dd.mm.YYYY HH:mm");
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
                    name = this.event.doctor.fullName;
                }
                return name;
            }
        }
    }
</script>