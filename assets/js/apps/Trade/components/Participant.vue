<template>
</template>


<script>
    export default {
        props: {
            user: {
                type: Object,
                required: false
            }
        },
        methods: {
            selectEvent: function (event) {
                this.$emit("selected", event);
            }
        }
    }
</script>