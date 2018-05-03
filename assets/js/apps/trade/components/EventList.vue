<template>
    <div class="row">
        <EventListItem
                v-for="event in events"
                v-bind:key="event.id"
                v-bind:event="event">
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
            eventSelected: function (event) {
                event.isSelected = !event.isSelected;
                //this.$forceUpdate();
            },
            selectEvent: function (event) {
                this.$emit("selected", event);
            }
        }
    }
</script>