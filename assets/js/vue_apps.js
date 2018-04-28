import Vue from 'vue'
import VueI18n from 'vue-i18n'

Vue.config.productionTip = false;

// translations
Vue.use(VueI18n);
// Ready translated locale messages
const messages = {
    de: {
        choose_frontend_user: 'Mitarbeiter auswählen',
        assign_events: 'Termine zuweisen',
        no_user_assigned: "Keinem Mitarbeiter zugewiesen",
        assign_all_events: "alle zuweisen"
    }
};

const i18n = new VueI18n({
    locale: 'de',
    messages,
});

import AssignApp from './apps/assign/assign'

new Vue({
    i18n,
    el: '#assign-app',
    template: '<AssignApp/>',
    components: {AssignApp}
});
