<template>
    <div>
        <div class="row" v-if="events.length > 8">
            <div :class="'col-md-' + size">
                <div class="form-group">
                    <input class="form-control" :title="$t('actions.filter_by_clinic_or_doctor_or_period')" type="text" v-model="filter"
                           :placeholder="$t('actions.filter_by_clinic_or_doctor_or_period')"/>
                </div>
            </div>
        </div>
        <div class="row">
            <EventTile
                    :class="'col-md-' + size"
                    v-for="event in filteredEvents"
                    :key="event.id"
                    :event="event"
                    :is-loading="loadingEvents.indexOf(event) >= 0"
                    :is-disabled="disabledEvents.indexOf(event) >= 0"
                    :is-selected="selectedEvents.indexOf(event) >= 0"
                    @select="$emit('event-selected', event)"
            >
            </EventTile>
            <div :class="'col-md-' + size" v-if="events.length === 0">
                <slot name="placeholder"></slot>
            </div>
        </div>
    </div>
</template>


<script>
    import EventTile from "./EventTile"
    import moment from "moment";
    moment.locale('de');


    export default {
        components: {
            EventTile
        },
        data() {
            return {
                filter: ""
            }
        },
        props: {
            events: {
                type: Array,
                required: true
            },
            selectedEvents: {
                type: Array,
                required: false,
                default: function () {
                    return [];
                }
            },
            loadingEvents: {
                tyoe: Array,
                required: false,
                default: function () {
                    return [];
                }
            },
            disabledEvents: {
                tyoe: Array,
                required: false,
                default: function () {
                    return [];
                }
            },
            size: {
                type: Number,
                default: 12,
                required: false
            }
        },
        methods: {
            formatDateTime: function (date) {
                return moment(date).format("DD.MM.YYYY HH:mm");
            }
        },
        computed: {
            eventIndex: function () {
                let index = [];
                this.events.forEach(event => {
                    let currentIndex = event.clinic.name.toLowerCase();
                    if (event.doctor !== null) {
                        currentIndex += event.doctor.fullName.toLowerCase();
                    }
                    currentIndex += this.formatDateTime(event.startDateTime);
                    currentIndex += this.formatDateTime(event.endDateTime);
                    index.push({
                        index: currentIndex,
                        event: event
                    })
                });

                return index;
            },
            filteredEvents: function () {
                if (this.filter.length === 0) {
                    return this.events;
                }
                const lowerCaseFilter = this.filter.toLowerCase();
                return this.eventIndex.filter(e => e.index.indexOf(lowerCaseFilter) >= 0).map(e => e.event);
            }
        }
    }
</script>