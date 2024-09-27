import React from 'react';
import * as WpBlocksApi from '@wordpress/blocks';
import CalendarIcon from '../Common/CalendarIcon';
import { connectionStatus } from '../../constants/leadinConfig';
import MeetingGutenbergPreview from './MeetingGutenbergPreview';
import MeetingSaveBlock from './MeetingSaveBlock';
import MeetingEdit from '../../shared/Meeting/MeetingEdit';
import ErrorHandler from '../../shared/Common/ErrorHandler';
import { __ } from '@wordpress/i18n';
import { isFullSiteEditor } from '../../utils/withMetaData';
import StylesheetErrorBondary from '../Common/StylesheetErrorBondary';

const ConnectionStatus = {
  Connected: 'Connected',
  NotConnected: 'NotConnected',
};

export interface IMeetingBlockAttributes {
  attributes: {
    url: string;
    preview?: boolean;
  };
}

export interface IMeetingBlockProps extends IMeetingBlockAttributes {
  setAttributes: Function;
  isSelected: boolean;
}

export default function registerMeetingBlock() {
  const editComponent = (props: IMeetingBlockProps) => {
    const isPreview = props.attributes.preview;
    const isConnected = connectionStatus === ConnectionStatus.Connected;
    return (
      <StylesheetErrorBondary>
        {isPreview ? (
          <MeetingGutenbergPreview />
        ) : isConnected ? (
          <MeetingEdit
            {...props}
            preview={true}
            origin="gutenberg"
            fullSiteEditor={isFullSiteEditor()}
          />
        ) : (
          <ErrorHandler status={401} />
        )}
      </StylesheetErrorBondary>
    );
  };

  // We do not support the full site editor: https://issues.hubspotcentral.com/browse/WP-1033
  if (!WpBlocksApi) {
    return null;
  }

  WpBlocksApi.registerBlockType('leadin/hubspot-meeting-block', {
    title: __('Hubspot Meetings Scheduler', 'leadin'),
    description: __(
      'Schedule meetings faster and forget the back-and-forth emails Your calendar stays full, and you stay productive',
      'leadin'
    ),
    icon: CalendarIcon,
    category: 'leadin-blocks',
    attributes: {
      url: {
        type: 'string',
        default: '',
      } as WpBlocksApi.BlockAttribute<string>,
      preview: {
        type: 'boolean',
        default: false,
      } as WpBlocksApi.BlockAttribute<boolean>,
    },
    example: {
      attributes: {
        preview: true,
      },
    },
    edit: editComponent,
    save: props => <MeetingSaveBlock {...props} />,
  });
}
