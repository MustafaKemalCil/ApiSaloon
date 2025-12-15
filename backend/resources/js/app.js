import './bootstrap';
import { createApp } from 'vue';
import ExampleComponent from './components/ExampleComponent.vue';

// Vue uygulamasını #app id'sine mount et
const app = createApp({});
app.component('example-component', ExampleComponent);
app.mount('#app');
