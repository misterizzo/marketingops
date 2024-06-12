import { styled } from '@linaria/react';

interface IUIContainerProps {
  textAlign?: string;
}

export default styled.div<IUIContainerProps>`
  text-align: ${props => (props.textAlign ? props.textAlign : 'inherit')};
`;
