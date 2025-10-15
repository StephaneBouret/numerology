import { startStimulusApp } from '@symfony/stimulus-bundle';
import AddressAutocompleteController from './controllers/address_autocomplete_controller.js';
import AppointmentTypeController from './controllers/appointment_type_controller.js';
import AjaxFormController from './controllers/ajax_form_controller.js';
import CalendarController from './controllers/calendar_controller.js';
import ToastController from './controllers/toast_controller.js';
import NamesFormatter from './controllers/names_formatter_controller.js';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
app.register('address-autocomplete', AddressAutocompleteController);
app.register('appointment-type', AppointmentTypeController);
app.register('ajax-form', AjaxFormController);
app.register('calendar', CalendarController);
app.register('toast', ToastController);
app.register('names-formatter', NamesFormatter);
