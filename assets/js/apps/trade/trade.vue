<template>
    <div id="trade-app">
        <div class="row">
            <div class="col-md-4">
                <p class="lead">{{ $t("choose_your_events") }}</p>
                <OptionEventSelectList v-bind:events="myEvents" v-bind:events-loading="myEventsLoading" v-bind:none-selected="noMine" />
            </div>
            <div class="col-md-4">
                <p class="lead">{{ $t("choose_their_events") }}</p>
                <OptionEventSelectList v-bind:events="theirEvents" v-bind:events-loading="theirEventsLoading" v-bind:none-selected="noTheirs" />
            </div>
            <div class="col-md-4">
                <p class="lead">{{ $t("your_trade") }}</p>
                <div v-if="sender != null && theirSelectedEvents.length > 0">
                    <Participant
                            v-bind:users="sender"/>
                    <p>{{$t("receives")}}</p>
                    <EventList
                            v-bind:events="theirSelectedEvents"
                            @selected="eventSelected">
                    </EventList>
                    <hr/>
                </div>

                <div v-if="receiver != null && mySelectedEvents.length > 0">
                    <Participant
                            v-bind:users="receiver"/>
                    <p>{{$t("receives")}}</p>
                    <EventList
                            v-bind:events="mySelectedEvents"
                            @selected="eventSelected">
                    </EventList>
                    <hr/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import EventList from "./components/EventList"
    import OptionEventSelectList from "./components/OptionalEventSelect"
    import {AtomSpinner} from 'epic-spinners'
    import axios from "axios"
    import Participant from "./components/Participant";

    export default {
        data() {
            return {
                myEvents: [],
                myEventsLoading: false,
                theirEvents: [],
                theirEventsLoading: false,
                noTheirs: false,
                noMine: false,
                sender: null,
                receiver: null
            }
        },
        components: {
            Participant,
            EventList,
            AtomSpinner,
            OptionEventSelectList
        },
        methods: {

        },
        computed: {
            mySelectedEvents: function () {
                return this.myEvents.filter(e => e.isSelected);
            },
            theirSelectedEvents: function () {
                return this.theirEvents.filter(e => e.isSelected);
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

            axios.get("/trade/api/possible_senders")
                .then((response) => {
                    this.sender = response.data;
                    console.log(this.sender)
                });
        },
    }

</script>