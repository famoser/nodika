<template>
    <div>
    <button v-on:click="$emit('select', frontendUser)">{{ frontendUser.fullName }}</button>
    <input v-model="frontendUser.fullName" title="full name">
    <input type="checkbox" v-model="frontendUser.isSelected" title="is selected"/>
    </div>
</template>


<script>
    export default {
        props: {
            frontendUser: {
                type: Object,
                required: true
            }
        }
    }
</script>