import { startStimulusApp } from '@symfony/stimulus-bundle';
import AddressAutocompleteController from './controllers/address_autocomplete_controller.js';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
app.register('address-autocomplete', AddressAutocompleteController);
