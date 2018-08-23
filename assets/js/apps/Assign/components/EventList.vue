<template>
    <div class="row">
        <Event
                v-for="event in events"
                v-bind:key="event.id"
                v-bind:event="event"
                v-bind:selected-doctor="selectedDoctor"
                @select="selectEvent(event)"
        >
        </Event>
        <div v-if="events.length === 0">
            <p>{{ $t("messages.no_events_for_doctor")}}</p>
        </div>
    </div>
</template>


<script>
    import Event from "./Event"

    export default {
        components: {
            Event
        },
        props: {
            selectedDoctor: {
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