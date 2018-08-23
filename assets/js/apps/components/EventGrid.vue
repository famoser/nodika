<template>
    <div class="row">
        <EventTile
                :class="'col-md-' + size"
                v-for="event in events"
                :key="event.id"
                :event="event"
                :is-loading="loadingEvents.includes(event)"
                :is-disabled="disabledEvents.includes(event)"
                :is-selected="selectedEvents.includes(event)"
                @select="$emit('event-selected', event)"
        >
        </EventTile>
        <div :class="'col-md-' + size" v-if="events.length === 0">
            <slot name="placeholder"></slot>
        </div>
    </div>
</template>


<script>
    import EventTile from "./EventTile"

    export default {
        components: {
            EventTile
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
        }
    }
</script>