import React, { Fragment } from 'react';
import AsyncSelect from '../Common/AsyncSelect';
import UISpacer from '../UIComponents/UISpacer';
import { __ } from '@wordpress/i18n';

interface IMeetingSelectorProps {
  options: any[];
  onChange: Function;
  value: any;
}

export default function MeetingSelector({
  options,
  onChange,
  value,
}: IMeetingSelectorProps) {
  const optionsWrapper = [
    {
      label: __('Meeting name', 'leadin'),
      options,
    },
  ];

  return (
    <Fragment>
      <UISpacer />
      <p data-test-id="leadin-meeting-select">
        <b>{__('Select a meeting scheduling page', 'leadin')}</b>
      </p>
      <AsyncSelect
        defaultOptions={optionsWrapper}
        onChange={onChange}
        placeholder={__('Select a meeting', 'leadin')}
        value={value}
      />
    </Fragment>
  );
}
