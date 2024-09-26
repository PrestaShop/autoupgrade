import HomePage from './pages/Home';
import RouteHandler from './routing/RouteHandler';

new RouteHandler().init();

const page = new HomePage();
page.mount();
