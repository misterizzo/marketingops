import React from 'react';
import { RawHTML } from '@wordpress/element';
import { IFormBlockAttributes } from './registerFormBlock';
import useCustomCssBlockProps from '../Common/useCustomCssBlockProps';

const DefaultCssClasses = 'wp-block-leadin-hubspot-form-block';

export default function FormSaveBlock({ attributes }: IFormBlockAttributes) {
  const { portalId, formId, embedVersion } = attributes;
  const blockProps = useCustomCssBlockProps(DefaultCssClasses);

  if (portalId && formId) {
    return (
      <RawHTML {...blockProps}>
        {`[hubspot portal="${portalId}" id="${formId}" version="${embedVersion}" type="form"]`}
      </RawHTML>
    );
  }
  return null;
}
