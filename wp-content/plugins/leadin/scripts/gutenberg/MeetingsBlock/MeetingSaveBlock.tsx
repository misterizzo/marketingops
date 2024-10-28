import React from 'react';
import { RawHTML } from '@wordpress/element';
import { IMeetingBlockAttributes } from './registerMeetingBlock';

import useCustomCssBlockProps from '../Common/useCustomCssBlockProps';

const DefaultCssClasses = 'wp-block-leadin-hubspot-meeting-block';

export default function MeetingSaveBlock({
  attributes,
}: IMeetingBlockAttributes) {
  const { url } = attributes;
  const blockProps = useCustomCssBlockProps(DefaultCssClasses);

  if (url) {
    return (
      <RawHTML
        {...blockProps}
      >{`[hubspot url="${url}" type="meeting"]`}</RawHTML>
    );
  }
  return null;
}
