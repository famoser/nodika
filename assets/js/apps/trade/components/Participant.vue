<template>
    <div class="row">
        <div class="col-md-12">
            {{ user.fullName }} <span class="text-secondary">{{ user.members[0].name }}</span>
        </div>
    </div>
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