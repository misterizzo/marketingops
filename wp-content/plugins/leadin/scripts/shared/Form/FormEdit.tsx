import React, { Fragment, useEffect } from 'react';
import { portalId, refreshToken } from '../../constants/leadinConfig';
import UISpacer from '../UIComponents/UISpacer';
import PreviewForm from './PreviewForm';
import FormSelect from './FormSelect';
import { IFormBlockProps } from '../../gutenberg/FormBlock/registerFormBlock';
import {
  usePostBackgroundMessage,
  BackgroudAppContext,
  useBackgroundAppContext,
} from '../../iframe/useBackgroundApp';
import { ProxyMessages } from '../../iframe/integratedMessages';
import LoadingBlock from '../Common/LoadingBlock';
import { getOrCreateBackgroundApp } from '../../utils/backgroundAppUtils';
import { isRefreshTokenAvailable } from '../../utils/isRefreshTokenAvailable';

interface IFormEditProps extends IFormBlockProps {
  preview: boolean;
  origin: 'gutenberg' | 'elementor';
  fullSiteEditor?: boolean;
}

function FormEdit({
  attributes,
  isSelected,
  setAttributes,
  preview = true,
  origin = 'gutenberg',
  fullSiteEditor,
}: IFormEditProps) {
  const { formId, formName, embedVersion } = attributes;
  const formSelected = portalId && formId;

  const isBackgroundAppReady = useBackgroundAppContext();
  const monitorFormPreviewRender = usePostBackgroundMessage();

  const handleChange = (selectedForm: {
    value: string;
    label: string;
    embedVersion?: string;
  }) => {
    setAttributes({
      portalId,
      formId: selectedForm.value,
      formName: selectedForm.label,
      embedVersion: selectedForm.embedVersion,
    });
  };

  useEffect(() => {
    monitorFormPreviewRender({
      key: ProxyMessages.TrackFormPreviewRender,
      payload: {
        origin,
      },
    });
  }, [origin]);

  return !isBackgroundAppReady ? (
    <LoadingBlock />
  ) : (
    <Fragment>
      {(isSelected || !formSelected) && (
        <FormSelect
          formId={formId}
          formName={formName}
          handleChange={handleChange}
          origin={origin}
          embedVersion={embedVersion}
        />
      )}
      {formSelected && (
        <Fragment>
          {isSelected && <UISpacer />}
          {preview && (
            <PreviewForm
              portalId={portalId}
              formId={formId}
              fullSiteEditor={fullSiteEditor}
              embedVersion={embedVersion}
            />
          )}
        </Fragment>
      )}
    </Fragment>
  );
}

export default function FormEditContainer(props: IFormEditProps) {
  return (
    <BackgroudAppContext.Provider
      value={
        isRefreshTokenAvailable() && getOrCreateBackgroundApp(refreshToken)
      }
    >
      <FormEdit {...props} />
    </BackgroudAppContext.Provider>
  );
}
