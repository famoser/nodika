<template>
    <div id="confirm-app">
        <div v-if="events.length > 0" class="container">
            <div class="row d-block mb-5 mt-5 border-top border-primary pt-5 border-bottom pb-5">
                <p class="lead">{{ $t("actions.confirm_events") }}</p>
                <EventGrid
                        :events="events"
                        :loading-events="loadingEvents"
                        :disabled-events="loadingEvents"
                        :size="3"
                        @event-selected="eventSelected">
                </EventGrid>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from "axios"
    import EventGrid from "../components/EventGrid"

    export default {
        data() {
            return {
                events: [],
                loadingEvents: []
            }
        },
        components: {
            EventGrid
        },
        methods: {
            eventSelected: function (event) {
                this.loadingEvents.push(event);
                axios.get("/api/confirm/event/" + event.id)
                    .then((response) => {
                        this.events = this.events.filter(e => {
                            return e.id !== event.id
                        });
                        this.loadingEvents.splice(this.loadingEvents.indexOf(event), 1);
                    });
            }
        },
        mounted() {
            axios.get("/api/confirm/events")
                .then((response) => {
                    this.events = response.data;
                });
        },
    }

</script>