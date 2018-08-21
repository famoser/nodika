<template>
    <div class="row">
        <EventListItem
                v-for="event in events"
                v-bind:key="event.id"
                v-bind:event="event"
                v-bind:selection-enabled="selectionEnabled">
        </EventListItem>
    </div>
</template>


<script>
    import EventListItem from "./EventListItem"

    export default {
        components: {
            EventListItem
        },
        props: {
            events: {
                type: Array,
                required: true
            },
            selectionEnabled: {
                type: Boolean,
                required: false,
                default: true
            }
        }
    }
</script>