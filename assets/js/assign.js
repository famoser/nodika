import Vue from "vue";

Vue.component('frontend-user-select', {
    props: {
        frontend_user: Object
    },
    template: '<button>{{ frontend_user.text }}</button>'
});

const app7 = new Vue({
    el: '#assign',
    data: {
        frontendUsers: [
            {id: 0, text: 'Vegetables'},
            {id: 1, text: 'Cheese'},
            {id: 2, text: 'Whatever else humans are supposed to eat'}
        ]
    }
});