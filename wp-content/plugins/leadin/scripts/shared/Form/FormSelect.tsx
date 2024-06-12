import React from 'react';
import FormSelector from './FormSelector';
import LoadingBlock from '../Common/LoadingBlock';
import { __ } from '@wordpress/i18n';
import useForms from './hooks/useForms';
import useCreateFormFromTemplate from './hooks/useCreateFormFromTemplate';
import { FormType, isDefaultForm } from '../../constants/defaultFormOptions';
import ErrorHandler from '../Common/ErrorHandler';

interface IFormSelectProps {
  formId: string;
  formName: string;
  handleChange: Function;
  origin: 'gutenberg' | 'elementor';
}

export default function FormSelect({
  formId,
  formName,
  handleChange,
  origin = 'gutenberg',
}: IFormSelectProps) {
  const { search, formApiError, reset } = useForms();
  const {
    createFormByTemplate,
    reset: createReset,
    isCreating,
    hasError,
    formApiError: createApiError,
  } = useCreateFormFromTemplate(origin);
  const value =
    formId && formName
      ? {
          label: formName,
          value: formId,
        }
      : null;

  const handleLocalChange = (option: { value: FormType }) => {
    if (isDefaultForm(option.value)) {
      createFormByTemplate(option.value).then(({ guid, name }) => {
        handleChange({
          value: guid,
          label: name,
        });
      });
    } else {
      handleChange(option);
    }
  };

  return isCreating ? (
    <LoadingBlock />
  ) : formApiError || createApiError ? (
    <ErrorHandler
      status={formApiError ? formApiError.status : createApiError.status}
      resetErrorState={() => {
        if (hasError) {
          createReset();
        } else {
          reset();
        }
      }}
      errorInfo={{
        header: __('There was a problem retrieving your forms', 'leadin'),
        message: __(
          'Please refresh your forms or try again in a few minutes',
          'leadin'
        ),
        action: __('Refresh forms', 'leadin'),
      }}
    />
  ) : (
    <FormSelector
      loadOptions={search}
      onChange={(option: { value: FormType }) => handleLocalChange(option)}
      value={value}
    />
  );
}
