export const FormMessages = {
  CreateFormAppNavigation: 'CREATE_FORM_APP_NAVIGATION',
} as const;

export type FormMessageType = typeof FormMessages[keyof typeof FormMessages];
