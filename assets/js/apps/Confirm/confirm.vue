<template>
    <div id="confirm-app">
        <div v-if="events.length > 0" class="container">
            <div class="row d-block mb-4 p-4 bg-light">
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
                        if (this.events.length === 0) {
                            window.location.reload();
                        }
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