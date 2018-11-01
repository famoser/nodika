// library import
import merge from 'deepmerge'

// get all shared lang files
import actions from './actions'
import clinic from './clinic'
import event from './event'
import generation from './generation'
import messages from './messages'
import offer from './offer'
import view from './view'

// merge in single object; mapping to the correct property
const translations = {actions, clinic, event, generation, messages, offer, view};
let sharedTranslations = {};
Object.keys(translations).forEach(property => {
    const translation = translations[property];
    Object.keys(translation).forEach(lang => {
        if (!(lang in sharedTranslations)) {
            sharedTranslations[lang] = {};
        }
        sharedTranslations[lang][property] = translation[lang]
    });
});

// merge passed messages with the shared translations
export default function (messages) {
    return merge(messages, sharedTranslations);
}