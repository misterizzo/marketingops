import $ from 'jquery';
import { domElements } from '../constants/selectors';

export default class ThickBoxModal {
  openTriggerSelector: string;
  inlineContentId: string;
  windowCssClass: string;
  contentCssClass: string;

  constructor(
    openTriggerSelector: string,
    inlineContentId: string,
    windowCssClass: string,
    contentCssClass: string
  ) {
    this.openTriggerSelector = openTriggerSelector;
    this.inlineContentId = inlineContentId;
    this.windowCssClass = windowCssClass;
    this.contentCssClass = contentCssClass;

    $(openTriggerSelector).on('click', this.init.bind(this));
  }

  close() {
    //@ts-expect-error global
    window.tb_remove();
  }

  init(e: Event) {
    //@ts-expect-error global
    window.tb_show(
      '',
      `#TB_inline?inlineId=${this.inlineContentId}&modal=true`
    );
    // thickbox doesn't respect the width and height url parameters https://core.trac.wordpress.org/ticket/17249
    // We override thickboxes css with !important in the css
    $(domElements.thickboxModalWindow).addClass(this.windowCssClass);

    // have to modify the css of the thickbox content container as well
    $(domElements.thickboxModalContent).addClass(this.contentCssClass);

    // we unbind previous handlers because a thickbox modal is a single global object.
    // Everytime it is re-opened, it still has old handlers bound
    $(domElements.thickboxModalClose)
      .off('click')
      .on('click', this.close);

    e.preventDefault();
  }
}
