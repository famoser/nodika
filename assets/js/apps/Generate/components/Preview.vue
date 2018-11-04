<template>
    <div>
        <div class="d-flex justify-content-between mb-4">
            <div>
                <button class="btn btn-outline-primary" @click="$emit('reload')">
                    {{$t("actions.reload")}}
                </button>
            </div>
            <div>
                <button class="btn btn-primary" @click="$emit('submit')">
                    {{$t("actions.save_and_finish")}}
                </button>
            </div>
        </div>
        <table class="table table-hover">
            <thead>
            <tr>
                <th> {{$t('event.owner')}}</th>
                <th> {{$t('event.start')}}</th>
                <th> {{$t('event.end')}}</th>
                <th v-if="generation.differentiateByEventType">{{$t('event.eventType')}}</th>
            </tr>
            </thead>
            <tbody>
            <EventRow :showEventType="generation.differentiateByEventType" v-for="event in events" :event="event" :key="event.id"/>
            </tbody>
        </table>
    </div>
</template>


<script>
    import moment from "moment";
    import EventRow from './EventRow'

    moment.locale('de');

    export default {
        components: {EventRow},
        props: {
            events: {
                type: Array,
                required: true
            },
            generation: {
                type: Object,
                required: true
            }
        }
    }
</script>