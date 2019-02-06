let Vue = require( 'vue/dist/vue.js' );
let BootstrapVue = require(  'bootstrap-vue' );
let indico = require( './components/indico-options.vue' );

Vue.use(BootstrapVue);

new Vue( {
    el: '#indico-options-main',
    components: {
        IndicoOptions: indico
    }
});