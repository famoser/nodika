<template>
    <div>
        <Doctor
                v-for="doctor in doctors"
                v-bind:key="doctor.id"
                v-bind:doctor="doctor"
                @select="selectDoctor(doctor)"
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
            }
        },
        methods: {
            selectDoctor: function (doctor) {
                this.doctors.forEach(function (f) {
                    f.isSelected = false;
                });
                doctor.isSelected = true;
                this.$emit("selection-changed", doctor);
            }
        }
    }
</script>