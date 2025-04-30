import { __ } from '@wordpress/i18n';

const BLANK_FORM = 'BLANK';
const NEWSLETTER_FORM = 'NEWSLETTER';
const CONTACT_US_FORM = 'CONTACT_US';
const EVENT_REGISTRATION_FORM = 'EVENT_REGISTRATION';
const TALK_TO_AN_EXPERT_FORM = 'TALK_TO_AN_EXPERT';
const BOOK_A_MEETING_FORM = 'BOOK_A_MEETING';
const GATED_CONTENT_FORM = 'GATED_CONTENT';

export type FormType =
  | typeof BLANK_FORM
  | typeof NEWSLETTER_FORM
  | typeof CONTACT_US_FORM
  | typeof EVENT_REGISTRATION_FORM
  | typeof TALK_TO_AN_EXPERT_FORM
  | typeof BOOK_A_MEETING_FORM
  | typeof GATED_CONTENT_FORM;

export const DEFAULT_OPTIONS = {
  label: __('Templates', 'leadin'),
  options: [
    { label: __('Blank Form', 'leadin'), value: BLANK_FORM },
    { label: __('Newsletter Form', 'leadin'), value: NEWSLETTER_FORM },
    { label: __('Contact Us Form', 'leadin'), value: CONTACT_US_FORM },
    {
      label: __('Event Registration Form', 'leadin'),
      value: EVENT_REGISTRATION_FORM,
    },
    {
      label: __('Talk to an Expert Form', 'leadin'),
      value: TALK_TO_AN_EXPERT_FORM,
    },
    { label: __('Book a Meeting Form', 'leadin'), value: BOOK_A_MEETING_FORM },
    { label: __('Gated Content Form', 'leadin'), value: GATED_CONTENT_FORM },
  ],
};

export function isDefaultForm(value: FormType) {
  return (
    value === BLANK_FORM ||
    value === NEWSLETTER_FORM ||
    value === CONTACT_US_FORM ||
    value === EVENT_REGISTRATION_FORM ||
    value === TALK_TO_AN_EXPERT_FORM ||
    value === BOOK_A_MEETING_FORM ||
    value === GATED_CONTENT_FORM
  );
}
