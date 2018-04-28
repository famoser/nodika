<template>
    <a href="#" v-on:click.prevent="$emit('select', frontendUser)" class="card" v-bind:class="{ 'border-primary' : frontendUser.isSelected }">
        <div class="card-body">
            <p>{{ frontendUser.fullName }}<br>
                <span class="text-secondary">{{ frontendUser.members.map(v => v.name).join(", ")}}</span>
            </p>
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