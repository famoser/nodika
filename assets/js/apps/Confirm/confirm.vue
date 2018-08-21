<template>
    <div id="confirm-app">
        <div v-if="events.length > 0" class="container">
            <div class="row d-block mb-5 mt-5 border-top border-danger pt-5 border-bottom pb-5">
                <p class="lead">{{ $t("confirm_events") }}</p>
                <EventList
                        v-bind:events="events"
                        @selected="eventSelected">
                </EventList>
            </div>
        </div>
    </div>
</template>

<script>
    import EventList from "./components/EventList"
    import axios from "axios"

    export default {
        data() {
            return {
                events: []
            }
        },
        components: {
            EventList
        },
        methods: {
            eventSelected: function (event) {
                event.isLoading = true;
                axios.get("/confirm/api/event/" + event.id)
                    .then((response) => {
                        this.events = this.events.filter(e => {
                            return e.id !== event.id
                        });
                    });
            }
        },
        mounted() {
            axios.get("/confirm/api/events")
                .then((response) => {
                    const events = response.data;
                    for (let i = 0; i < events.length; i++) {
                        events[i].isLoading = false;
                    }
                    this.events = events;
                });
        },
    }

</script>