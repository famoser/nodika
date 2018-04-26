import Vue from 'vue'
Vue.config.productionTip = false;

import AssignApp from './apps/assign'
new Vue({
    el: '#assign-app',
    template: '<AssignApp/>',
    components: { AssignApp }
});
