<template>
    <div class="row">
        <EventSelectableListItem
                v-for="event in events"
                v-bind:key="event.id"
                v-bind:event="event"
                v-bind:selected-frontend-user="selectedFrontendUser"
                @select="selectEvent(event)"
        >
        </EventSelectableListItem>
    </div>
</template>


<script>
    import EventSelectableListItem from "./EventSelectableListItem"

    export default {
        components: {
            EventSelectableListItem
        },
        props: {
            selectedFrontendUser: {
                type: Object,
                required: true
            },
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