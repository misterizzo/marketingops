import { withSelect, withDispatch, select } from '@wordpress/data';

// from answer here: https://github.com/WordPress/gutenberg/issues/44477#issuecomment-1263026599
export const isFullSiteEditor = () => {
  return select && !!select('core/edit-site');
};

const applyWithSelect: any = withSelect((select: Function, props: any): any => {
  return {
    metaValue: select('core/editor').getEditedPostAttribute('meta')[
      props.metaKey
    ],
  };
});

const applyWithDispatch: any = withDispatch(
  (dispatch: Function, props: any): any => {
    return {
      setMetaValue(value: string) {
        dispatch('core/editor').editPost({ meta: { [props.metaKey]: value } });
      },
    };
  }
);

function apply<T>(el: T): T {
  return applyWithSelect(applyWithDispatch(el));
}

export default apply;
