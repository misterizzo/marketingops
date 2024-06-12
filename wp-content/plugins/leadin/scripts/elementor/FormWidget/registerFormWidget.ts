import ReactDOM from 'react-dom';
import FormControlController from './FormControlController';
import FormWidgetController from './FormWidgetController';

export interface IFormAttributes {
  formId: string;
  formName: string;
  portalId: string;
}

export default class registerFormWidget {
  widgetContainer: Element;
  attributes: IFormAttributes;
  controlContainer: Element;
  setValue: Function;

  constructor(controlContainer: any, widgetContainer: any, setValue: Function) {
    const attributes = widgetContainer.dataset.attributes
      ? JSON.parse(widgetContainer.dataset.attributes)
      : {};

    this.widgetContainer = widgetContainer;
    this.controlContainer = controlContainer;
    this.setValue = setValue;
    this.attributes = attributes;
  }

  render() {
    ReactDOM.render(
      FormWidgetController(this.attributes, this.setValue)(),
      this.widgetContainer
    );

    ReactDOM.render(
      FormControlController(this.attributes, this.setValue)(),
      this.controlContainer
    );
  }

  done() {
    ReactDOM.unmountComponentAtNode(this.widgetContainer);
    ReactDOM.unmountComponentAtNode(this.controlContainer);
  }
}
