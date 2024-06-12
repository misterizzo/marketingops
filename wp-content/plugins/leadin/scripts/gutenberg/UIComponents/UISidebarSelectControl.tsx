import React from 'react';

import { SelectControl } from '@wordpress/components';
import withMetaData from '../../utils/withMetaData';
import {
  useBackgroundAppContext,
  usePostBackgroundMessage,
} from '../../iframe/useBackgroundApp';
import { ProxyMessages } from '../../iframe/integratedMessages';

interface IOption {
  label: string;
  value: string;
  disabled?: boolean;
}

interface IUISidebarSelectControlProps {
  metaValue?: string;
  metaKey: string;
  setMetaValue?: Function;
  options: IOption[];
  className: string;
  label: JSX.Element;
}

const UISidebarSelectControl = (props: IUISidebarSelectControlProps) => {
  const isBackgroundAppReady = useBackgroundAppContext();

  const monitorSidebarMetaChange = usePostBackgroundMessage();

  return (
    <SelectControl
      value={props.metaValue}
      onChange={content => {
        if (props.setMetaValue) {
          props.setMetaValue(content);
        }
        isBackgroundAppReady &&
          monitorSidebarMetaChange({
            key: ProxyMessages.TrackSidebarMetaChange,
            payload: {
              metaKey: props.metaKey,
            },
          });
      }}
      {...props}
    />
  );
};

export default withMetaData(UISidebarSelectControl);
