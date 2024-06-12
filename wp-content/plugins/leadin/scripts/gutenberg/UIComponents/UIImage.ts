import { styled } from '@linaria/react';

export default styled.img`
  height: ${props => (props.height ? props.height : 'auto')};
  width: ${props => (props.width ? props.width : 'auto')};
`;
