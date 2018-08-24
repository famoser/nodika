<template>
    <div>
        <Doctor
                v-for="doctor in doctors"
                :key="doctor.id"
                :doctor="doctor"
                :isSelected="selectedDoctor === doctor"
                @select="$emit('doctor-selected', arguments[0])"
        >
        </Doctor>
    </div>
</template>


<script>
    import Doctor from "./Doctor"

    export default {
        components: {
            Doctor
        },
        props: {
            doctors: {
                type: Array,
                required: true
            },
            selectedDoctor: {
                type: Object,
                required: false
            }
        }
    }
</script>