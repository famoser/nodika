<template>
    <div>
        <div class="d-flex justify-content-between mb-5">
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
        <h3>{{$t('event_target.entity.plural')}}</h3>
        <table class="table table-hover mb-5">
            <thead>
            <tr>
                <th> {{$t('event_target.name')}}</th>
                <th> {{$t('event_target.weight')}}</th>
                <th> {{$t('event_target.absolute_score')}}</th>
                <th> {{$t('event_target.relative_score')}}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="clinic in generation.clinics">
                <td>{{clinic.clinic.name}}</td>
                <td>{{clinic.weight}}</td>
                <td>{{clinic.generationScore}}</td>
                <td>{{clinic.generationScore / clinic.weight}}</td>
            </tr>
            </tbody>
        </table>
        <h3>{{$t('event.entity.plural')}}</h3>
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
            <EventRow :showEventType="generation.differentiateByEventType" v-for="event in generation.previewEvents" :event="event" :key="event.id"/>
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
            generation: {
                type: Object,
                required: true
            }
        }, mounted() {
            console.log(this.generation);
        }
    }
</script>