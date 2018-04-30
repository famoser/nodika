<template>
    <div class="col-md-12">
        <a href="#" v-on:click.prevent="$emit('select', event)" class="card"
           v-bind:class="{ 'border-primary' : event.isSelected }">
            <div class="card-header">
                {{ formatDateTime(event.startDateTime) }} - {{ formatDateTime(event.endDateTime) }}
            </div>
            <div class="card-body">
                <p>
                    {{ displayFrontendUser(event.frontendUser) || $t('no_user_assigned') }}<br/>
                    <span class="text-secondary">{{event.member.name}}</span>
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
            }
        },
        methods: {
            formatDateTime: function (date) {
                return format(date, ["DD.MM.YYYY HH:mm"])
            },
            displayFrontendUser: function (user) {
                if (user == null) {
                    return null;
                }
                return user.fullName;
            }
        }
    }
</script>