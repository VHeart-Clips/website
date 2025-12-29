import i18n from 'i18next';
import I18nextBrowserLanguageDetector from 'i18next-browser-languagedetector';
import I18NextHttpBackend from 'i18next-http-backend';
import I18NextMultiloadBackendAdapter from 'i18next-multiload-backend-adapter';
import { initReactI18next } from 'react-i18next';

console.log(import.meta);
const hash = import.meta.env.DEV ? Date.now().toString(16) : '';

i18n.use(I18NextMultiloadBackendAdapter)
    .use(initReactI18next)
    .use(I18nextBrowserLanguageDetector)
    .init({
        load: 'languageOnly',
        preload: ['en', 'de'],
        ns: ['strings'],
        react: {
            //useSuspense: false,
        },
        detection: {
            order: ['htmlTag'],
            htmlTag: document.documentElement,
        },
        debug: import.meta.env.DEV,
        //lng: 'en',
        fallbackLng: 'en',
        keySeparator: '.',
        backend: {
            backend: I18NextHttpBackend,
            backendOption: {
                loadPath:
                    '/locales.json?locale={{lng}}&namespace={{ns}}',
                queryStringParams: import.meta.env.DEV ? { hash } : null, // only add cache buster in dev mode
                allowMultiLoading: true,
                cache: 'default',
            },
        },
        interpolation: {
            // Per i18n-react documentation: this is not needed since React is already
            // handling escapes for us.
            escapeValue: false,
        },
    });

export default i18n;
