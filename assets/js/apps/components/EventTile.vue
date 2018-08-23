<template>
    <div>
        <a href="#" v-on:click.prevent="$emit('select', event)" class="card"
           v-bind:class="{ 'border-primary' : isSelected, 'disabled' : event.isLoading || isDisabled, 'border-warning' : isLoading }">
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
            isLoading: {
                type: Boolean,
                required: false,
                default: false
            },
            isDisabled: {
                type: Boolean,
                required: false,
                default: false
            },
            isSelected: {
                type: Boolean,
                required: false,
                default: false
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