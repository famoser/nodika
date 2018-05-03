<template>
    <div id="trade-app">
        <div class="row">
            <div class="col-md-4">
                <p class="lead">{{ $t("choose_your_events") }}</p>
                <OptionEventSelectList v-bind:events="myEvents" v-bind:events-loading="myEventsLoading"
                                       @none_selected_changed="noMineAssigned"/>
            </div>
            <div class="col-md-4">
                <p class="lead">{{ $t("choose_their_events") }}</p>
                <OptionEventSelectList v-bind:events="theirEvents" v-bind:events-loading="theirEventsLoading"
                                       @none_selected_changed="noTheirsAssigned"/>
            </div>
            <div class="col-md-4">
                <p class="lead">{{ $t("your_trade") }}</p>
                <div v-if="theirSelectedEvents.length > 0 || noTheirs">
                    <TradePartner v-bind:users="possibleSenders" v-bind:selected-user="selectedSender"
                                  v-bind:users-loading="senderLoading" v-bind:selected-member="senderMember"
                                  v-bind:events="theirSelectedEvents"
                                  v-bind:verify-events="mySelectedEvents"
                                  @state_valid="senderValidChanged"/>
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
    import TradePartner from "./components/TradePartner";

    export default {
        data() {
            return {
                myEvents: [],
                myEventsLoading: true,
                noMine: false,
                theirEvents: [],
                theirEventsLoading: true,
                noTheirs: false,
                possibleSenders: [],
                selectedSender: null,
                senderLoading: true,
                senderMember: null,
                senderValid: true,
                possibleReceivers: [],
                selectedReceiver: null,
                receiverLoading: true
            }
        },
        components: {
            TradePartner,
            Participant,
            EventList,
            AtomSpinner,
            OptionEventSelectList
        },
        methods: {
            senderValidChanged: function (state) {
                this.senderValid = state;
            },
            noMineAssigned: function (state) {
                this.noMine = state
            },
            noTheirsAssigned: function (state) {
                this.noTheirs = state
            }
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
            axios.get("/trade/api/my_events")
                .then((response) => {
                    const events = response.data;
                    for (let i = 0; i < events.length; i++) {
                        events[i].isSelected = false;
                    }
                    this.myEvents = events;
                    this.myEventsLoading = false;
                });

            axios.get("/trade/api/their_events")
                .then((response) => {
                    const events = response.data;
                    for (let i = 0; i < events.length; i++) {
                        events[i].isSelected = false;
                    }
                    this.theirEvents = events;
                    this.theirEventsLoading = false;
                });

            axios.get("/trade/api/user")
                .then((response) => {
                    const sender = response.data;
                    this.possibleSenders = [sender];
                    this.selectedSender = sender;
                    this.senderLoading = false;
                });
        },
    }

</script>