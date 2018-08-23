<template>
    <div class="col-md-6">
        <a href="#" v-on:click.prevent="$emit('select', event)" class="card"
           v-bind:class="{ 'border-primary disabled' : isAssigned, 'disabled' : event.isLoading, 'border-warning' : event.isLoading }">
            <div class="card-header">
                {{ formatDateTime(event.startDateTime) }} - {{ formatDateTime(event.endDateTime) }}
          </div>
            <div class="card-body">
                <p>
                    {{ displayDoctor(event.doctor) || $t('event.no_user_assigned') }}<br/>
                    <span class="text-secondary">{{event.clinic.name}}</span>
                </p>
            </div>
        </a>
    </div>
</template>


<script>
    import format from 'date-fns/format'

    export default {
        props: {
            event: {
                type: Object,
                required: true
            },
            selectedDoctor: {
                type: Object,
                required: true
            }
        },
        computed: {
            isAssigned: function () {
                return this.alreadyAssigned(this.event);
            }
        },
        methods: {
            formatDateTime: function (date) {
                return format(date, ["DD.MM.YYYY HH:mm"])
            },
            displayDoctor: function (user) {
                if (user == null) {
                    return null;
                }
                return user.fullName;
            },
            alreadyAssigned: function (event) {
                return event.doctor != null && event.doctor.id === this.selectedDoctor.id;
            }
        }
    }
</script>