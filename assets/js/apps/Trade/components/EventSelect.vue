<template>
    <div>
        <EventGrid :events="owner.events"
                   :selected-events="owner.selectedEvents"
                   @event-selected="toggleMembership(owner.selectedEvents, arguments[0])"/>
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