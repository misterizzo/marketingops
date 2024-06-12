export default function elementorWidget(
  elementor: any,
  options: any,
  callback: Function,
  done = () => {}
) {
  return elementor.modules.controls.BaseData.extend({
    onReady() {
      const self = this;
      const controlContainer = this.ui.contentEditable.prevObject[0].querySelector(
        options.controlSelector
      );
      let widgetContainer = this.options.element.$el[0].querySelector(
        options.containerSelector
      );
      if (widgetContainer) {
        callback(controlContainer, widgetContainer, (args: any) =>
          self.setValue(args)
        );
      } else {
        //@ts-expect-error global
        window.elementorFrontend.hooks.addAction(
          `frontend/element_ready/${options.widgetName}.default`,
          (element: HTMLElement[]) => {
            widgetContainer = element[0].querySelector(
              options.containerSelector
            );
            callback(controlContainer, widgetContainer, (args: any) =>
              self.setValue(args)
            );
          }
        );
      }
    },
    saveValue(props: any) {
      this.setValue(props);
    },
    onBeforeDestroy() {
      //@ts-expect-error global
      window.elementorFrontend.hooks.removeAction(
        `frontend/element_ready/${options.widgetName}.default`
      );
      done();
    },
  });
}
