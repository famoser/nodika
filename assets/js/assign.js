import Vue from "vue";

Vue.component('frontend-user-select', {
    props: {
        frontend_user: Object
    },
    template: '' +
    '<div>' +
    '<button v-on:click="$emit(\'select\')" >{{ frontend_user.text }}</button>' +
    '<input type="text" :value="frontend_user.isSelected">' +
    '</div>'
});

const app7 = new Vue({
    el: '#assign',
    data: {
        frontendUsers: [
            {id: 0, text: 'Vegetables', isSelected: false},
            {id: 1, text: 'Cheese', isSelected: false},
            {id: 2, text: 'Whatever else humans are supposed to eat', isSelected: false}
        ]
    },
    computed: {
        isAnySelected: function () {
            let any = false;
            for (const user in this.frontendUsers) {
                any |= user.isSelected
            }
            return any;
        }
    },
    methods: {
        selectFrontendUser: function (frontendUser) {
            frontendUser.isSelected = !frontendUser.isSelected;
        }
    }
});