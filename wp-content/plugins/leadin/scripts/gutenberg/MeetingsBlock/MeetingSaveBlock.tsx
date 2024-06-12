import React from 'react';
import { RawHTML } from '@wordpress/element';
import { IMeetingBlockAttributes } from './registerMeetingBlock';

export default function MeetingSaveBlock({
  attributes,
}: IMeetingBlockAttributes) {
  const { url } = attributes;

  if (url) {
    return (
      <RawHTML className="wp-block-leadin-hubspot-meeting-block">{`[hubspot url="${url}" type="meeting"]`}</RawHTML>
    );
  }
  return null;
}
