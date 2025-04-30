export interface IForm {
  guid: string;
  name: string;
  embedVersion: string;
}

export enum HubSpotFormTemplateAvailabilityKeys {
  AI_GENERATED = 'ai-generated',
  BLANK = 'blank',
  NEWSLETTER = 'newsletter',
  CONTACT_US = 'contact-us',
  EVENT_REGISTRATION = 'event-registration',
  TALK_TO_AN_EXPERT = 'talk-to-an-expert',
  BOOK_A_MEETING = 'book-a-meeting',
  GATED_CONTENT = 'gated-content',
  SUPPORT = 'support',
}

export enum ExcludedTemplateAvailabilityKeys {
  SUPPORT = 'support',
  AI_GENERATED = 'ai-generated',
}

export const TemplateLabels = {
  [HubSpotFormTemplateAvailabilityKeys.BLANK]: 'Blank Form',
  [HubSpotFormTemplateAvailabilityKeys.NEWSLETTER]: 'Newsletter Form',
  [HubSpotFormTemplateAvailabilityKeys.CONTACT_US]: 'Contact Us Form',
  [HubSpotFormTemplateAvailabilityKeys.EVENT_REGISTRATION]:
    'Event Registration Form',
  [HubSpotFormTemplateAvailabilityKeys.TALK_TO_AN_EXPERT]:
    'Talk to an Expert Form',
  [HubSpotFormTemplateAvailabilityKeys.BOOK_A_MEETING]: 'Book a Meeting Form',
  [HubSpotFormTemplateAvailabilityKeys.GATED_CONTENT]: 'Gated Content Form',
};

export const TemplateValues = {
  [HubSpotFormTemplateAvailabilityKeys.BLANK]: 'BLANK',
  [HubSpotFormTemplateAvailabilityKeys.NEWSLETTER]: 'NEWSLETTER',
  [HubSpotFormTemplateAvailabilityKeys.CONTACT_US]: 'CONTACT_US',
  [HubSpotFormTemplateAvailabilityKeys.EVENT_REGISTRATION]:
    'EVENT_REGISTRATION',
  [HubSpotFormTemplateAvailabilityKeys.TALK_TO_AN_EXPERT]: 'TALK_TO_AN_EXPERT',
  [HubSpotFormTemplateAvailabilityKeys.BOOK_A_MEETING]: 'BOOK_A_MEETING',
  [HubSpotFormTemplateAvailabilityKeys.GATED_CONTENT]: 'GATED_CONTENT',
};

export type HubSpotFormTemplateAvailability = {
  canCreateWithMissingScopes: boolean;
  previewImageUrl: string;
  missingScopes: Array<string>;
};

export type TemplateAvailability = Record<
  HubSpotFormTemplateAvailabilityKeys,
  HubSpotFormTemplateAvailability
>;

export type TemplateAvailabilityResponse = {
  templateAvailability: TemplateAvailability;
};
