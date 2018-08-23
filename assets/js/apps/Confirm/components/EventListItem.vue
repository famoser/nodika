<template>
    <a href="#" v-on:click.prevent="$emit('select', event)" class="card"
       v-bind:class="{ 'border-success disabled' : event.isLoading }">
        <div class="card-header">
            {{ formatDateTime(event.startDateTime) }} - {{ formatDateTime(event.endDateTime) }}
        </div>
        <div class="card-body">
            <p>
                {{ displaydoctor(event.doctor) || $t('no_user_assigned') }}<br/>
                <span class="text-secondary">{{event.clinic.name}}</span>
            </p>
        </div>
    </a>
</template>


<script>
    import format from 'date-fns/format'

    export default {
        props: {
            event: {
                type: Object,
                required: true
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
            }
        }
    }
</script>