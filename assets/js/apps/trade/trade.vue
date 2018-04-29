<template>
    <div id="trade-app">
        <div class="row">
            <div class="col-md-4">
                <p class="lead">{{ $t("choose_your_events") }}</p>
                <AtomSpinner
                        v-if="myEventsLoading"
                        :animation-duration="1000"
                        :size="60"
                        :color="'#007bff'"
                />

                <EventList
                        v-bind:events="myEvents"
                        @selected="eventSelected">
                </EventList>
            </div>
            <div class="col-md-4">
                <p class="lead">{{ $t("choose_their_events") }}</p>
                <AtomSpinner
                        v-if="theirEventsLoading"
                        :animation-duration="1000"
                        :size="60"
                        :color="'#007bff'"
                />
                <EventList
                        v-bind:events="theirEvents"
                        @selected="eventSelected">
                </EventList>
            </div>
        </div>
    </div>
</template>

<script>
    import EventList from "./components/EventList"
    import {AtomSpinner} from 'epic-spinners'
    import axios from "axios"

    export default {
        data() {
            return {
                myEvents: [],
                myEventsLoading: false,
                theirEvents: [],
                theirEventsLoading: false
            }
        },
        components: {
            EventList,
            AtomSpinner
        },
        methods: {
            eventSelected: function (event) {
                event.isLoading = true;
                console.log("changed");
            }
        },
        mounted() {
            this.myEventsLoading = true;
            axios.get("/trade/api/my_events")
                .then((response) => {
                    const events = response.data;
                    for (let i = 0; i < events.length; i++) {
                        events[i].isSelected = false;
                    }
                    this.myEvents = events;
                    this.myEventsLoading = false;
                });

            this.theirEventsLoading = true;
            axios.get("/trade/api/their_events")
                .then((response) => {
                    const events = response.data;
                    for (let i = 0; i < events.length; i++) {
                        events[i].isSelected = false;
                    }
                    this.theirEvents = events;
                    this.theirEventsLoading = false;
                });
        },
    }

</script>