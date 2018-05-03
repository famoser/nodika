import Vue from 'vue'
import VueI18n from 'vue-i18n'

Vue.config.productionTip = false;

// translations
Vue.use(VueI18n);

//assign app
import AssignApp from './apps/assign/assign'

if (document.getElementById("assign-app") != null) {
    const messagesAssign = {
        de: {
            choose_frontend_user: 'Mitarbeiter auswählen',
            assign_events: 'Termine zuweisen',
            no_user_assigned: "Keinem Mitarbeiter zugewiesen",
            assign_all_events: "alle zuweisen",
            no_events: 'Keine Termine zu dieser Auswahl gefunden'
        }
    };

    const i18nAssign = new VueI18n({
        locale: 'de',
        messages: messagesAssign,
    });

    new Vue({
        i18n: i18nAssign,
        el: '#assign-app',
        template: '<AssignApp/>',
        components: {AssignApp}
    });
}

//confirm app
import ConfirmApp from './apps/confirm/confirm'

if (document.getElementById("confirm-app") != null) {
    const messagesCofirm = {
        de: {
            confirm_events: "Termine bestätigen",
            no_user_assigned: "Keinem Mitarbeiter zugewiesen"
        }
    };

    const i18nConfirm = new VueI18n({
        locale: 'de',
        messages: messagesCofirm,
    });

    new Vue({
        i18n: i18nConfirm,
        el: '#confirm-app',
        template: '<ConfirmApp/>',
        components: {ConfirmApp}
    });
}

//trade app
import TradeApp from './apps/trade/trade'

if (document.getElementById("trade-app") != null) {
    const messagesTrade = {
        de: {
            choose_your_events: "Aus Ihren Terminen auswählen",
            choose_their_events: "Termine Ihres Tauschpartners auswählen",
            no_user_assigned: "Keinem Mitarbeiter zugewiesen",
            your_trade: "Ihre Tauchanfrage",
            receives: "bekommt neu folgende Termine: ",
            select_no_events: "Keine Termine auswählen"
        }
    };

    const i18nTrade = new VueI18n({
        locale: 'de',
        messages: messagesTrade,
    });

    new Vue({
        i18n: i18nTrade,
        el: '#trade-app',
        template: '<TradeApp/>',
        components: {TradeApp}
    });
}