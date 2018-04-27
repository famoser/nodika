<template>
    <a href="#" v-on:click.prevent="$emit('select', frontendUser)" class="card" v-bind:class="{ 'border-primary' : frontendUser.isSelected }">
        <div class="card-body">
            <p>{{ frontendUser.fullName }}</p>
        </div>
    </a>
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