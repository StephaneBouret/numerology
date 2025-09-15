import { startStimulusApp } from '@symfony/stimulus-bundle';

const app = startStimulusApp();
import { startStimulusApp } from '@symfony/stimulus-bundle';
import AddressAutocompleteController from './controllers/address_autocomplete_controller.js';
import AppointmentTypeController from './controllers/appointment_type_controller.js';
import AjaxFormController from './controllers/ajax_form_controller.js';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
app.register('address-autocomplete', AddressAutocompleteController);
app.register('appointment-type', AppointmentTypeController);
app.register('ajax-form', AjaxFormController);
