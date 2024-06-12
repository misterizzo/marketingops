import React, { useState, useEffect, Fragment } from 'react';
import { portalId, refreshToken } from '../../constants/leadinConfig';
import ElementorBanner from '../Common/ElementorBanner';
import UISpinner from '../../shared/UIComponents/UISpinner';
import { __ } from '@wordpress/i18n';
import {
  BackgroudAppContext,
  useBackgroundAppContext,
} from '../../iframe/useBackgroundApp';
import useForms from './hooks/useForms';
import { getOrCreateBackgroundApp } from '../../utils/backgroundAppUtils';

interface IElementorFormSelectProps {
  formId: string;
  setAttributes: Function;
}

function ElementorFormSelect({
  formId,
  setAttributes,
}: IElementorFormSelectProps) {
  const { hasError, forms, loading } = useForms();

  return loading ? (
    <div>
      <UISpinner />
    </div>
  ) : hasError ? (
    <ElementorBanner type="danger">
      {__('Please refresh your forms or try again in a few minutes', 'leadin')}
    </ElementorBanner>
  ) : (
    <select
      value={formId}
      onChange={event => {
        const selectedForm = forms.find(
          form => form.value === event.target.value
        );
        if (selectedForm) {
          setAttributes({
            portalId,
            formId: selectedForm.value,
            formName: selectedForm.label,
          });
        }
      }}
    >
      <option value="" disabled={true} selected={true}>
        {__('Search for a form', 'leadin')}
      </option>
      {forms.map(form => (
        <option key={form.value} value={form.value}>
          {form.label}
        </option>
      ))}
    </select>
  );
}

function ElementorFormSelectWrapper(props: IElementorFormSelectProps) {
  const isBackgroundAppReady = useBackgroundAppContext();

  return (
    <Fragment>
      {!isBackgroundAppReady ? (
        <div>
          <UISpinner />
        </div>
      ) : (
        <ElementorFormSelect {...props} />
      )}
    </Fragment>
  );
}

export default function ElementorFormSelectContainer(
  props: IElementorFormSelectProps
) {
  return (
    <BackgroudAppContext.Provider
      value={refreshToken && getOrCreateBackgroundApp(refreshToken)}
    >
      <ElementorFormSelectWrapper {...props} />
    </BackgroudAppContext.Provider>
  );
}
