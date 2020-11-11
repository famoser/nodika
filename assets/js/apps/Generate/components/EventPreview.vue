<template>
    <div>
        <table class="table table-hover table-striped">
            <thead>
            <tr>
                <th v-if="ownerAssigned"> {{$t('event.owner')}}</th>
                <th> {{$t('event.start')}}</th>
                <th> {{$t('event.end')}}</th>
            </tr>
            </thead>
            <tbody>
            <EventRow v-for="event in eventsPreview" :event="event" :key="event.id"/>
            <template v-if="tooManyEventsCount > 0">
                <tr>
                    <td :colspan="ownerAssigned ? 3 : 2">
                        {{$t('events.skipping_events', {count: tooManyEventsCount})}}
                    </td>
                </tr>
                <EventRow v-for="event in lastEventsPreview" :event="event" :key="event.id"/>
            </template>
            </tbody>
        </table>
    </div>
</template>


<script>
    import moment from "moment";
    import EventRow from './EventRow'

    moment.locale('de');

    export default {
        props: {
            events: {
                type: Array,
                required: true
            }
        },
        components: {
            EventRow
        },
        data() {
            return {
                previewEventsCount: 4
            }
        },
        computed: {
            eventsPreview: function () {
                if (this.tooManyEventsCount > 0) {
                    return this.events.filter((_, index) => index < this.previewEventsCount);
                }
                return this.events;
            },
            tooManyEventsCount: function () {
                return this.events.length - 2 * this.previewEventsCount;
            },
            lastEventsPreview: function () {
                let minIndex = this.events.length - this.previewEventsCount;
                return this.events.filter((_, index) => index >= minIndex);
            },
            ownerAssigned: function () {
                return this.events.length > 0 && (this.events[0].clinic || this.events[0].doctor);
            }
        }
    }
</script>
