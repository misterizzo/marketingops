import { useBlockProps } from '@wordpress/block-editor';

export default function useCustomCssBlockProps(defaultCssClasses: string) {
  const blockProps = useBlockProps.save();
  if (
    !blockProps.className ||
    !(blockProps.className as string).includes(defaultCssClasses)
  ) {
    blockProps.className = `${blockProps.className} ${defaultCssClasses}`;
  }
  return blockProps;
}
