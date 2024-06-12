import { __ } from '@wordpress/i18n';

const REGISTRATION_FORM = 'REGISTRATION_FORM';
const CONTACT_US_FORM = 'CONTACT_US_FORM';
const NEWSLETTER_FORM = 'NEWSLETTER_FORM';
const SUPPORT_FORM = 'SUPPORT_FORM';
const EVENT_FORM = 'EVENT_FORM';

export type FormType =
  | typeof REGISTRATION_FORM
  | typeof CONTACT_US_FORM
  | typeof NEWSLETTER_FORM
  | typeof SUPPORT_FORM
  | typeof EVENT_FORM;

export const DEFAULT_OPTIONS = {
  label: __('Templates', 'leadin'),
  options: [
    { label: __('Registration Form', 'leadin'), value: REGISTRATION_FORM },
    { label: __('Contact us Form', 'leadin'), value: CONTACT_US_FORM },
    { label: __('Newsletter sign-up Form', 'leadin'), value: NEWSLETTER_FORM },
    { label: __('Support Form', 'leadin'), value: SUPPORT_FORM },
    { label: __('Event Registration Form', 'leadin'), value: EVENT_FORM },
  ],
};

export function isDefaultForm(value: FormType) {
  return (
    value === REGISTRATION_FORM ||
    value === CONTACT_US_FORM ||
    value === NEWSLETTER_FORM ||
    value === SUPPORT_FORM ||
    value === EVENT_FORM
  );
}
