import React from 'react';
import { RawHTML } from '@wordpress/element';
import { IFormBlockAttributes } from './registerFormBlock';

export default function FormSaveBlock({ attributes }: IFormBlockAttributes) {
  const { portalId, formId } = attributes;

  if (portalId && formId) {
    return (
      <RawHTML className="wp-block-leadin-hubspot-form-block">
        {`[hubspot portal="${portalId}" id="${formId}" type="form"]`}
      </RawHTML>
    );
  }
  return null;
}
