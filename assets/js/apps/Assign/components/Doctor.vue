<template>
    <a href="#" v-on:click.prevent="$emit('select', doctor)" class="card" v-bind:class="{ 'border-primary' : doctor.isSelected }">
        <div class="card-body">
            <p>{{ doctor.fullName }}<br>
                <span class="text-secondary">{{ doctor.clinics.map(v => v.name).join(", ")}}</span>
            </p>
        </div>
    </a>
</template>


<script>
    export default {
        props: {
            doctor: {
                type: Object,
                required: true
            }
        }
    }
</script>