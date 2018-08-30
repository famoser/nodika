<template>
    <div>
        <div class="row pb-4">
            <div class="col-md-12">
                <a href="#" class="card"
                   v-on:click.prevent="owner.noneSelected = !owner.noneSelected"
                   v-bind:class="{ 'border-primary' : owner.noneSelected }">
                    <div class="card-body">
                        <p>{{ $t('actions.select_no_events') }} </p>
                    </div>
                </a>
            </div>
        </div>

        <EventGrid :events="owner.events"
                   :selected-events="owner.selectedEvents"
                   @event-selected="toggleMembership(owner.selectedEvents, arguments[0])"
                   v-if="!owner.noneSelected"/>
    </div>
</template>


<script>
    import EventGrid from "../../components/EventGrid"

    export default {
        props: {
            owner: {
                type: Object,
                required: true
            }
        },
        methods: {
            toggleMembership: function (array, element) {
                const elementIndex = array.indexOf(element);
                if (elementIndex >= 0) {
                    array.splice(elementIndex, 1);
                } else {
                    array.push(element);
                }
            }
        },
        components: {
            EventGrid,
        }
    }
</script>