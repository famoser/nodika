<template>
    <div class="d-flex align-items-start justify-content-start">
        <EventListItem
                v-for="event in events"
                v-bind:key="event.id"
                v-bind:event="event"
                @select="selectEvent(event)">
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
                required: false
            }
        },
        methods: {
            selectEvent: function (event) {
                this.$emit("selected", event);
            }
        }
    }
</script>