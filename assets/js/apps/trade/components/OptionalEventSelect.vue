<template>
    <div>
        <div class="row">
            <div class="col-md-12">
                <a href="#" class="card"
                   v-on:click.prevent="noMineSelected()"
                   v-bind:class="{ 'border-primary' : noneSelected }">
                    <div class="card-body">
                        <p>{{ $t('select_no_events') }} </p>
                    </div>
                </a>
            </div>
        </div>

        <AtomSpinner
                v-if="eventsLoading"
                :animation-duration="1000"
                :size="60"
                :color="'#007bff'"
        />

        <EventList
                v-if="!noneSelected"
                v-bind:events="events">
        </EventList>
    </div>
</template>


<script>
    import EventList from "./EventList"
    import {AtomSpinner} from 'epic-spinners'

    export default {
        components: {
            EventList,
            AtomSpinner
        },
        props: {
            events: {
                type: Array,
                required: true
            },
            eventsLoading: {
                type: Boolean,
                required: true
            },
            noneSelected: {
                type: Boolean,
                required: true
            }
        },
        methods: {
            noMineSelected: function() {
                this.noneSelected = !this.noneSelected;
            }
        }
    }
</script>