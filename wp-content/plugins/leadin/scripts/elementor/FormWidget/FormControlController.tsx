import React, { Fragment } from 'react';
import { connectionStatus } from '../../constants/leadinConfig';
import ConnectPluginBanner from '../Common/ConnectPluginBanner';
import ElementorFormSelect from './ElementorFormSelect';
import { IFormAttributes } from './registerFormWidget';

const ConnectionStatus = {
  Connected: 'Connected',
  NotConnected: 'NotConnected',
};

export default function FormControlController(
  attributes: IFormAttributes,
  setValue: Function
) {
  return () => {
    const render = () => {
      if (connectionStatus === ConnectionStatus.Connected) {
        return (
          <ElementorFormSelect
            formId={attributes.formId}
            setAttributes={setValue}
          />
        );
      } else {
        return <ConnectPluginBanner />;
      }
    };
    return <Fragment>{render()}</Fragment>;
  };
}
